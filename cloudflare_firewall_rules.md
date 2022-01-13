# Cloudflare Anti-DDoS Ruleset
A collection of Cloudflare Firewall rules with common datacenter/commercial ASN blocks and known bad useragents.

## Rule Name: CAPTCHA High Risk Hosting

Expression: `((ip.geoip.asnum in {4134 8560 33333 20473 62240 36352 51167 14061 62567 26347 53667 26496 30083 24940 11588 33438 41732 6939 28753 30633 394380 60781 7203 63949 32244 19871 16276 35540 40676 15395 3842 12876 36351 12989 46562 36476 40021 141995}) and not cf.client.bot)`


## Rule Name: CAPTCHA Cloud Services

Expression: `((ip.geoip.asnum in {14618 16509 199883 15169 136907 8074 8075 39798 11414 20347 50889 60977 31898 843}) and not cf.client.bot)`


## Rule Name: CAPTCHA Hosting

Expression: `((ip.geoip.asnum in {20278 30823 39351 56994 4250 51430 32489 206980 20150 57230 29838 59764 33083 136258 52113 63199 10439 33330 19084 15510 54489 17216 22769 33302 19624 31798 33387 60068 13739 51395 16628 63018 61317 64245 46816 17318 62540 10297 11831 22552 10929 18779 54500 18978 30693 206804 27640 32181 36666 19844 53850 29802 54540 51290 35758 30475 37963 395089 39326 8972 20738 33182 63473 20021 25926 7489 29302 32780 54290 53597 25780 200039 25369 53755 20860 25820 14127 15083 133480 19318 19969 51659 395111 35908 46475 35916 54455 53889 63008 24961 60011 54104 17439 7040 15003 31727 19531 8492 22439 42331 22724 40861 198047 61157 42708 30176 8100 46261 35662 33070 58305 20454 19437 23352 55286 46652 50673 7979 46844 29550 32475 25521 5539 197226 6724 62471 35017 394256 40156 51159 36024 40244 11878 16125 62282 61053 42831 13213 23342 33724 36114 53340 46664 62217 60175 40824 33480 18450 29854 32097 45671 49981 23033 48095 56106 209181 21859 32613 397373 13354}) and not cf.client.bot and not http.request.version in {"SPDY/3.1" "HTTP/3" "HTTP/2"})`


## Rule Name: CAPTCHA VPN Services

Expression: `((ip.geoip.asnum in {57773 201665 53559 8473 41204 46805 52219 57858 57972 60485 63119 9009 41564 58065 13926 22781 54203 62651 63128 42624}) and not http.request.version in {"SPDY/3.1" "HTTP/3" "HTTP/2"})`


## Rule Name: CAPTCHA HTTP Proxies

Expression: `(http.x_forwarded_for contains "." and not cf.client.bot)`


## Rule Name: CAPTCHA Suspicious UA

Expression: `(http.user_agent contains "okhttp") or (http.user_agent contains "nikto") or (http.user_agent contains "httrack") or (http.user_agent contains "burp") or (http.user_agent contains "wget") or (http.user_agent contains "metasploit") or (http.user_agent contains "grabber") or (http.user_agent contains "skipfish") or (http.user_agent contains "screaming") or (http.user_agent contains "scrapy") or (http.user_agent contains "heritrix") or (http.user_agent contains "xapian") or (http.user_agent contains "postman") or (http.user_agent contains "wordpress") or (http.user_agent contains "archive") or (http.user_agent contains "nmap") or (http.user_agent contains "pinterest") or (http.user_agent contains "download") or (http.user_agent contains "sucker") or (http.user_agent contains "snag") or (http.user_agent contains "check") or (http.user_agent contains "grab") or (http.user_agent contains "java") or (http.user_agent contains "PHP") or (http.user_agent contains "Perl") or (http.user_agent contains "PECL") or (http.user_agent contains "urllib") or (http.user_agent contains "snoopy") or (http.user_agent contains "steeler") or (http.user_agent contains "SBIder") or (http.user_agent contains "lwp") or (http.user_agent contains "sitecheck") or (http.user_agent contains "curl") or (http.user_agent contains "acunetix") or (http.user_agent contains "offline") or (http.user_agent contains "strings") or (http.user_agent contains "HTMLParser") or (http.user_agent contains "Copier") or (http.user_agent contains "EmailWolf") or (http.user_agent contains "Gregarius") or (http.user_agent contains "wii") or (http.user_agent contains "libnup") or (http.user_agent contains "Dillo") or (http.user_agent contains "Download") or (http.user_agent contains "Morfeus") or (http.user_agent contains "ZmEu") or (http.user_agent contains "fuck") or (http.user_agent contains "shit") or (http.user_agent contains "Gimmie") or (http.user_agent contains "larbin") or (http.user_agent contains "offline") or (http.user_agent contains "Fuzz") or (http.user_agent contains "Survey") or (http.user_agent contains "HeadlessChrome") or (http.user_agent contains "Reaper") or (http.user_agent contains "Teleport") or (http.user_agent contains "Extractor") or (http.user_agent contains "riddler") or (http.user_agent contains "Go-http") or (http.user_agent contains "Rest") or (http.user_agent contains "python") or (http.user_agent contains "Apache") or (http.user_agent contains "apimon") or (http.user_agent contains "Nuclei") or (http.user_agent contains "CheckHost")`
