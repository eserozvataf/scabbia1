{
    "info": {
        "name":             "Database",
        "version":          "1.1.0",
        "license":          "GPLv3",
        "phpversion":       "5.3.0",
        "phpdependList":    [],
        "fwversion":        "1.1",
        "fwdependList":     [
            "Cache",
            "Datasources",
            "String"
        ]
    },
    "eventList": [
        {
            "name":         "load",
            "type":         "callback",
            "value":        "Scabbia\\Extensions\\Database\\Database::extensionLoad"
        }
    ]
}
