vcl 4.0;

import std;

backend default {
    .host = "localhost";
    .port = "80";
}

acl ban {
    "localhost";
    "127.0.0.1";
}

sub vcl_recv {
    # Ban
    if (req.method == "BAN") {
        if (client.ip !~ ban) {
            return (synth(405, "Method not allowed"));
        }
        if (!req.http.X-Ban-Host) {
            return (synth(400, "Please specify X-Ban-Host header"));
        }
        if (req.http.X-Ban-Tags) {            
            ban("obj.http.X-Ban-Tags ~ " + req.http.X-Ban-Tags + " && obj.http.X-Ban-Host ~ " + req.http.X-Ban-Host);
            return (synth(200, "Banned by Tags: " + req.http.X-Ban-Tags + ", Host: " + req.http.X-Ban-Host));
        } elsif (req.http.X-Ban-URL) {
            ban("obj.http.X-Ban-URL ~ " + req.http.X-Ban-URL + " && obj.http.X-Ban-Host ~ " + req.http.X-Ban-Host);
            return (synth(200, "Banned by URL: " + req.http.X-Ban-URL + ", Host: " + req.http.X-Ban-Host));
        }
        return (synth(400, "Please specify X-Ban-URL or X-Ban-Tags headers"));
    }

    if (
        req.method != "GET" &&
        req.method != "HEAD" &&
        req.method != "PUT" &&
        req.method != "POST" &&
        req.method != "TRACE" &&
        req.method != "OPTIONS" &&
        req.method != "DELETE"
    ) {
          /* Non-RFC2616 or CONNECT which is weird. */
        return (pipe);
    }
    
    # tell backend support for esi
    set req.http.Surrogate-Capability = "varnish=ESI/1.0";

    # We only deal with GET and HEAD by default
    if (req.method != "GET" && req.method != "HEAD") {
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
    if (req.http.Authorization) {
        return (pass);
    }

    # remove Google gclid parameters
    set req.url = regsuball(req.url,"\?gclid=[^&]+$",""); # strips when QS = "?gclid=AAA"
    set req.url = regsuball(req.url,"\?gclid=[^&]+&","?"); # strips when QS = "?gclid=AAA&foo=bar"
    set req.url = regsuball(req.url,"&gclid=[^&]+",""); # strips when QS = "?foo=bar&gclid=AAA" or QS = "?foo=bar&gclid=AAA&bar=baz"

    return (hash);
}

sub vcl_backend_response {
    # Enable esi processing 
    if (beresp.http.Surrogate-Control ~ "ESI/1.0") {
        unset beresp.http.Surrogate-Control;
        set beresp.do_esi = true;
    }

    set beresp.http.X-Ban-URL = bereq.url;
    set beresp.http.X-Ban-Host = bereq.http.host;

    if (beresp.http.X-Cache-Debug) {
        set beresp.http.X-Cache-Control = beresp.http.Cache-Control;
        set beresp.http.X-TTL = beresp.ttl;
    }

    if (
        beresp.status == 200 || 
        beresp.status == 301 || 
        beresp.status == 404
    ) {
        if (
            beresp.http.Content-Type ~ "text/html" || 
            beresp.http.Content-Type ~ "text/xml"  ||
            beresp.http.Content-Type ~ "application/json"
        ) {
            if (beresp.ttl < 1s) {
                set beresp.ttl = 0s;
                set beresp.uncacheable = true;
                return (deliver);
            }
            set beresp.http.magicmarker = "1";
            unset beresp.http.set-cookie;
        } else {
            set beresp.ttl = 4h;
        }
        return (deliver);
    }
    
    set beresp.ttl = 0s;
    set beresp.uncacheable = true;
    
    return (deliver);
}

sub vcl_deliver {
    if (resp.http.X-Cache-Debug) {
        if (obj.hits > 0) {
            set resp.http.X-Cache = "HIT";
            set resp.http.X-Cache-Hits = obj.hits;
        } else {
            set resp.http.X-Cache = "MISS";
        }
    } else {
        unset resp.http.Age;
        unset resp.http.X-Cache-Debug;
        unset resp.http.X-Ban-Tags;        
        unset resp.http.X-Ban-Host;
        unset resp.http.X-Ban-URL;
        unset resp.http.X-Powered-By;
        unset resp.http.Server;
        unset resp.http.X-Varnish;
        unset resp.http.Via;
        unset resp.http.Link;
    }
    
    if (resp.http.magicmarker) {
        unset resp.http.magicmarker;

        set resp.http.Cache-Control = "no-store, no-cache, must-revalidate, post-check=0, pre-check=0";
        set resp.http.Pragma = "no-cache";
        set resp.http.Expires = "Mon, 31 Mar 2008 10:00:00 GMT";
    }
}
