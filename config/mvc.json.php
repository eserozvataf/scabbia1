{
    "mvc": {
        "defaultController":    "home",
        "defaultAction":        "index",
        "link":                 "{@siteroot}/{@controller}/{@action}{@params}{@query}",

        "view": {
            "namePattern":          "{@path}{@controller}/{@action}.{@extension}",
            "defaultViewExtension": "php",

            "viewEngineList:development": [
                {
                    "extension":    "md",
                    "class":        "viewEngineMarkdown"
                }
            ]
        }
    }
}