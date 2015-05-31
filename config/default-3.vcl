backend default {
  .host = "127.0.0.1";
  .port = "8080";
}

acl purge {
    "localhost";
    "127.0.0.1";
}

sub vcl_recv {
    if (req.restarts == 0) {
        if (req.http.x-forwarded-for) {
            set req.http.X-Forwarded-For = req.http.X-Forwarded-For + ", " + client.ip;
        } else {
            set req.http.X-Forwarded-For = client.ip;
        }
    }
    
    # Purge
    if (req.method == "PURGE") {
        if (client.ip !~ purge) {
            return error 405, "Method not allowed";
        }
        if (!req.http.X-Purge-Host) {
            return error 400, "Please specify X-Purge-Host header";
        }
        if (req.http.X-Purge-Tags) {            
            ban("obj.http.X-Purge-Tags ~ " + req.http.X-Purge-Tags + " && obj.http.X-Purge-Host ~ " + req.http.X-Purge-Host);
            return error 200, "Purged by Tags: " + req.http.X-Purge-Tags + ", Host: " + req.http.X-Purge-Host;
        } elsif (req.http.X-Purge-URL) {
            ban("obj.http.X-Purge-URL ~ " + req.http.X-Purge-URL + " && obj.http.X-Purge-Host ~ " + req.http.X-Purge-Host);
            return error 200, "Purged by URL: " + req.http.X-Purge-URL + ", Host: " + req.http.X-Purge-Host;
        }
        return error 400, "Please specify X-Purge-URL or X-Purge-Tags headers";
    }    
    
    set req.http.Surrogate-Capability = "varnish=ESI/1.0";
    
    if (req.request != "GET" &&
      req.request != "HEAD" &&
      req.request != "PUT" &&
      req.request != "POST" &&
      req.request != "TRACE" &&
      req.request != "OPTIONS" &&
      req.request != "DELETE" &&
      req.request != "PURGE") {
        /* Non-RFC2616 or CONNECT which is weird. */
        return (pipe);
    }
    
    # we only deal with GET and HEAD by default    
    if (req.request != "GET" && req.request != "HEAD") {
        return (pass);
    }
    
    # normalize url in case of leading HTTP scheme and domain
    set req.url = regsub(req.url, "^http[s]?://[^/]+", "");
    
    # static files are always cacheable. remove SSL flag and cookie
    if (req.url ~ "\.(css|js|jpg|png|gif|tiff|bmp|gz|tgz|bz2|tbz|mp3|ogg|svg|swf|woff)(\?|$)") {
        unset req.http.Https;
        unset req.http.Cookie;
    }

    # not cacheable by default
    if (req.http.Authorization || req.http.Https) {
        return (pass);
    }

    # normalize Aceept-Encoding header
    # http://varnish.projects.linpro.no/wiki/FAQ/Compression
    if (req.http.Accept-Encoding) {
        if (req.url ~ "\.(jpg|png|gif|gz|tgz|bz2|tbz|mp3|ogg|swf|flv)$") {
            # No point in compressing these
            remove req.http.Accept-Encoding;
        } elsif (req.http.Accept-Encoding ~ "gzip") {
            set req.http.Accept-Encoding = "gzip";
        } elsif (req.http.Accept-Encoding ~ "deflate" && req.http.user-agent !~ "MSIE") {
            set req.http.Accept-Encoding = "deflate";
        } else {
            # unkown algorithm
            remove req.http.Accept-Encoding;
        }
    }
    
    # remove Google gclid parameters
    set req.url = regsuball(req.url,"\?gclid=[^&]+$",""); # strips when QS = "?gclid=AAA"
    set req.url = regsuball(req.url,"\?gclid=[^&]+&","?"); # strips when QS = "?gclid=AAA&foo=bar"
    set req.url = regsuball(req.url,"&gclid=[^&]+",""); # strips when QS = "?foo=bar&gclid=AAA" or QS = "?foo=bar&gclid=AAA&bar=baz"
  
    return (lookup);
}

sub vcl_fetch {
    # Enable esi processing 
    if (beresp.http.Surrogate-Control ~ "ESI/1.0") {
        unset beresp.http.Surrogate-Control;
        set beresp.do_esi = true;
    }

    # add ban-lurker tags to object
    set beresp.http.X-Purge-URL = req.url;
    set beresp.http.X-Purge-Host = req.http.host;
    
    if (
        beresp.status == 200 || 
        beresp.status == 301 || 
        beresp.status == 404
    ) {
        if (
            beresp.http.Content-Type ~ "text/html" || 
            beresp.http.Content-Type ~ "text/xml" ||
            beresp.http.Content-Type ~ "application/json"
        ) {
            if (beresp.ttl < 1s) {
                set beresp.ttl = 0s;
                return (hit_for_pass);
            }
            set beresp.http.magicmarker = "1";
            unset beresp.http.set-cookie;
        } else {
            set beresp.ttl = 4h;
        }
        return (deliver);
    }
    return (hit_for_pass);
}

sub vcl_deliver {
    # debug info
    unset resp.http.x-url;
    unset resp.http.x-host;
    if (resp.http.X-Cache-Debug) {
        if (obj.hits > 0) {
            set resp.http.X-Cache = "HIT";
            set resp.http.X-Cache-Hits = obj.hits;
        } else {
           set resp.http.X-Cache = "MISS";
        }
        set resp.http.X-Cache-Expires = resp.http.Expires;
    } else {
        # remove Varnish/proxy header
        remove resp.http.X-Varnish;
        remove resp.http.Via;
        remove resp.http.Age;
        remove resp.http.X-Purge-URL;
        remove resp.http.X-Purge-Host;
    }
    
    if (resp.http.magicmarker) {
        unset resp.http.magicmarker;
        set resp.http.Cache-Control = "no-store, no-cache, must-revalidate, post-check=0, pre-check=0";
        set resp.http.Pragma = "no-cache";
        set resp.http.Expires = "Mon, 31 Mar 2008 10:00:00 GMT";
        set resp.http.Age = "0";
    }
}
