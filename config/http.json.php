{
    "http": {
        "link":         "{@siteroot}/{@path}",

        "rewriteList": [
            {
                "limitMethods":     [ "get" ],
                "match":            "regions/([a-z_]+)/([a-z_]+)/([a-z0-9\\-]+)",
                "forward":          "firms/$3"
            }
        ],

        "routeList": [
            {
                "match":            "(controller:alnum)?(params:/+[^?&.]*)?(format:\\.[^?&]*)?(query:[?&].*)?",
                "callback":         "Scabbia\\Extensions\\Mvc\\mvc::route"
            }
        ],

        "errorPages": {
            "notfound":     "{app}views/shared/error.php",
            "restriction":  "{app}views/shared/error.php",
            "maintenance":  "{app}views/shared/error.php",
            "ipban":        "{app}views/shared/error.php",
            "error":        "{app}views/shared/error.php"
        },

        "userAgents": {
            "autoCheck": "0",

            "platformList": [
                {
                    "match":    "windows|winnt|win95|win98",
                    "name":     "Windows"
                },
                {
                    "match":    "os x|ppc mac|ppc",
                    "name":     "MacOS"
                },
                {
                    "match":    "irix|netbsd|freebsd|openbsd|bsdi|unix|sunos|linux|debian|gnu",
                    "name":     "Unix"
                }
            ],

            "crawlerList": [
                {
                    "type":     "bot",
                    "match":    "googlebot|msnbot|slurp|yahoo|askjeeves|fastcrawler|infoseek|lycos",
                    "name":     "Searchbot"
                },
                {
                    "type":     "browser",
                    "match":    "Opera",
                    "name":     "Opera"
                },
                {
                    "type":     "browser",
                    "match":    "Mozilla|Firefox|Firebird|Phoenix",
                    "name":     "Firefox"
                },
                {
                    "type":     "browser",
                    "match":    "MSIE|Internet Explorer",
                    "name":     "Internet Explorer"
                },
                {
                    "type":     "browser",
                    "match":    "Flock",
                    "name":     "Flock"
                },
                {
                    "type":     "browser",
                    "match":    "Chrome",
                    "name":     "Chrome"
                },
                {
                    "type":     "browser",
                    "match":    "Shiira",
                    "name":     "Shiira"
                },
                {
                    "type":     "browser",
                    "match":    "Chimera",
                    "name":     "Chimera"
                },
                {
                    "type":     "browser",
                    "match":    "Camino",
                    "name":     "Camino"
                },
                {
                    "type":     "browser",
                    "match":    "Netscape",
                    "name":     "Netscape"
                },
                {
                    "type":     "browser",
                    "match":    "OmniWeb",
                    "name":     "OmniWeb"
                },
                {
                    "type":     "browser",
                    "match":    "Safari",
                    "name":     "Safari"
                },
                {
                    "type":     "browser",
                    "match":    "Konqueror",
                    "name":     "Konqueror"
                },
                {
                    "type":     "browser",
                    "match":    "icab",
                    "name":     "iCab"
                },
                {
                    "type":     "browser",
                    "match":    "Lynx",
                    "name":     "Lynx"
                },
                {
                    "type":     "browser",
                    "match":    "Links",
                    "name":     "Links"
                },
                {
                    "type":     "browser",
                    "match":    "hotjava",
                    "name":     "HotJava"
                },
                {
                    "type":     "browser",
                    "match":    "amaya",
                    "name":     "Amaya"
                },
                {
                    "type":     "browser",
                    "match":    "IBrowse",
                    "name":     "IBrowse"
                },
                {
                    "type":     "mobile",
                    "match":    "palm|elaine",
                    "name":     "Palm"
                },
                {
                    "type":     "mobile",
                    "match":    "iphone|ipod",
                    "name":     "iOS"
                },
                {
                    "type":     "mobile",
                    "match":    "blackberry",
                    "name":     "Blackberry"
                },
                {
                    "type":     "mobile",
                    "match":    "symbian|series60",
                    "name":     "SymbianOS"
                },
                {
                    "type":     "mobile",
                    "match":    "windows ce",
                    "name":     "Windows CE"
                },
                {
                    "type":     "mobile",
                    "match":    "opera mini|operamini",
                    "name":     "Opera Mini"
                },
                {
                    "type":     "mobile",
                    "match":    "mobile|wireless|j2me|phone",
                    "name":     "Other Mobile"
                }
            ]
        }
    },

    "session": {
        "cookie": {
            "name":     "sessid",
            "life":     "0",
            "ipCheck":  "0",
            "uaCheck":  "1"
        }
    },

    "access": {
        "maintenance": {
            "mode":         "0",
            "page":         "{app}views/static_maintenance.php",
            "mvcpage":      "shared/maintenance.cshtml",
            "ipExcludeList": [
                "127.0.0.1",
                "127.0.0.2"
            ]
        },

        "ipFilter": {
            "page":         "{app}views/static_ipban.php",
            "mvcpage":      "shared/ipban.cshtml",
            "ipFilterList": [
                {
                    "type":     "deny",
                    "pattern":  "188.0.0.?"
                },
                {
                    "type":     "allow",
                    "pattern":  "*.*.*.*"
                }
            ]
        }
    }
}