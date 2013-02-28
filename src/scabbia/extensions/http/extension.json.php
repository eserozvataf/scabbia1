{
    "info": {
        "name":             "Http",
        "version":          "1.1.0",
        "license":          "GPLv3",
        "phpversion":       "5.3.0",
        "phpdependList":    [],
        "fwversion":        "1.1",
        "fwdependList":     [
            "String"
        ]
    },
    "eventList": [
        {
            "name":         "load",
            "type":         "callback",
            "value":        "Scabbia\\Extensions\\Http\\Request::extensionLoad"
        },
        {
            "name":         "output",
            "type":         "callback",
            "value":        "Scabbia\\Extensions\\Http\\Http::output"
        }
    ]
}
