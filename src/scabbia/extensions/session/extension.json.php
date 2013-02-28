{
    "info": {
        "name":             "Session",
        "version":          "1.1.0",
        "license":          "GPLv3",
        "phpversion":       "5.3.0",
        "phpdependList":    [],
        "fwversion":        "1.1",
        "fwdependList":     [
            "Cache"
        ]
    },
    "eventList": [
        {
            "name":         "output",
            "type":         "callback",
            "value":        "Scabbia\\Extensions\\Session\\Session::save"
        }

    ]
}