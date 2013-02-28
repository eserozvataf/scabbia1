{
    "info": {
        "name":             "Blackmore",
        "version":          "1.1.0",
        "license":          "GPLv3",
        "phpversion":       "5.3.0",
        "phpdependList":    [],
        "fwversion":        "1.1",
        "fwdependList":     [
            "Auth",
            "Http",
            "Resources",
            "String",
            "Validation",
            "Zmodels"
        ]
    },
    "eventList": [
        {
            "name":         "registerControllers",
            "type":         "loadClass",
            "value":        "Scabbia\\Extensions\\Blackmore\\Blackmore"
        },
        {
            "name":         "blackmoreRegisterModules",
            "type":         "callback",
            "value":        "Scabbia\\Extensions\\Blackmore\\BlackmoreScabbia::blackmoreRegisterModules"
        },
        {
            "name":         "blackmoreRegisterModules",
            "type":         "callback",
            "value":        "Scabbia\\Extensions\\Blackmore\\BlackmoreZmodels::blackmoreRegisterModules"
        }
    ]
}
