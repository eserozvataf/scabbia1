{
    "includeList":          [],
    "downloadList":         [],

    "includeList:disabled": [
        "{core}controllers/*.php",
        "{core}models/*.php"
    ],

    "options": {
        "gzip":         "1",
        "autoload":     "0",
        "siteroot:disabled":   "/sampleapp"
    },

    "i8n": {
        "languages": [
            {
                "id":               "en",
                "locale":           "en_US.UTF-8",
                "localewin":        "English_United States.1252",
                "internalEncoding": "UTF-8",
                "name":             "English"
            },
            {
                "id":               "tr",
                "locale":           "tr_TR.UTF-8",
                "localewin":        "Turkish_Turkey.1254",
                "internalEncoding": "UTF-8",
                "name":             "Turkish"
            }
        ]
    },

    "logger": {
        "filename":     "{date|'d-m-Y'} {@category}.txt",
        "line":         "[{date|'d-m-Y H:i:s'}] {strtoupper|@category} | {@ip} | {@message}"
    },

    "cache": {
        "keyphase":     "",
        "storage":      "memcache://192.168.2.4:11211"
    },

    "smtp": {
        "host":         "ssl://mail.messagingengine.com",
        "port":         "465",
        "username":     "eser@sent.com",
        "password":     ""
    }
}