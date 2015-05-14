vcl 4.0;

import std;
# The minimal Varnish version is 4.0

backend default {
    .host = "localhost";
    .port = "8080";
}

acl purge {
    "localhost";
    "127.0.0.1";
}

sub vcl_recv {
    # Purge
    if (req.method == "PURGE") {
        if (client.ip !~ purge) {
            return (synth(405, "Method not allowed"));
        }
        if (!req.http.X-Purge-Host) {
            return (synth(400, "Please specify X-Purge-Host header"));
        }
        if (req.http.X-Purge-Tags) {            
            ban("obj.http.X-Purge-Tags ~ " + req.http.X-Purge-Tags + " && obj.http.X-Purge-Host ~ " + req.http.X-Purge-Host);
            return (synth(200, "Purged by Tags: " + req.http.X-Purge-Tags + ", Host: " + req.http.X-Purge-Host));
        } elsif (req.http.X-Purge-URL) {
            ban("obj.http.X-Purge-URL ~ " + req.http.X-Purge-URL + " && obj.http.X-Purge-Host ~ " + req.http.X-Purge-Host);
            return (synth(200, "Purged by URL: " + req.http.X-Purge-URL + ", Host: " + req.http.X-Purge-Host));
        }
        return (synth(400, "Please specify X-Purge-URL or X-Purge-Tags headers"));
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

    return (hash);
}

sub vcl_backend_response {
    # Enable esi processing 
    if (beresp.http.Surrogate-Control ~ "ESI/1.0") {
        unset beresp.http.Surrogate-Control;
        set beresp.do_esi = true;
    }

    # cache only successfully responses
    if (beresp.status != 200) {
        set beresp.ttl = 0s;
        set beresp.uncacheable = true;
        return (deliver);
    }

    set beresp.http.X-Purge-URL = bereq.url;
    set beresp.http.X-Purge-Host = bereq.http.host;

    if (beresp.http.X-Cache-Debug) {
        set beresp.http.X-Cache-Control = beresp.http.Cache-Control;
        set beresp.http.X-TTL = beresp.ttl;
    }

    # validate if we need to cache it and prevent from setting cookie
    # images, css and js are cacheable by default so we have to remove cookie also
    if (beresp.ttl > 0s && (bereq.method == "GET" || bereq.method == "HEAD")) {
        unset beresp.http.set-cookie;
        if (bereq.url !~ "\.(css|js|jpg|png|gif|tiff|bmp|gz|tgz|bz2|tbz|mp3|ogg|svg|swf|woff)(\?|$)") {
            set beresp.http.Pragma = "no-cache";
            set beresp.http.Expires = "-1";
            set beresp.http.Cache-Control = "no-store, no-cache, must-revalidate, max-age=0";
        }
    }
    return (deliver);
}

sub vcl_deliver {
    if (resp.http.X-Cache-Debug) {
        if (obj.hits > 0) {
            set resp.http.X-Cache-Debug = "HIT";
            set resp.http.X-Cache-Hits = obj.hits;
        } else {
            set resp.http.X-Cache-Debug = "MISS";
        }
    } else {
        unset resp.http.Age;
        unset resp.http.X-Cache-Debug;
        unset resp.http.X-Purge-Tags;        
        unset resp.http.X-Purge-Host;
        unset resp.http.X-Purge-URL;
        unset resp.http.X-Powered-By;
        unset resp.http.Server;
        unset resp.http.X-Varnish;
        unset resp.http.Via;
        unset resp.http.Link;
    }
}

