# Server
```
useradd lsws
chown -Rv lsws:lsws /var/www/pterodactyl
```
Create file `/usr/local/lsws/conf/vhosts/yourdomain.com.conf`:
```
docRoot                   /var/www/pterodactyl/public
vhDomain                  yourdomain.com
adminEmails               noreply@yourdomain.com
enableGzip                1
cgroups                   0

errorlog /usr/local/lsws/logs/pterodactyl.app-error.log {
  useServer               0
  logLevel                ERROR
  rollingSize             100M
}

accesslog /usr/local/lsws/logs/pterodactyl.app-access.log {
  useServer               0
  logFormat               %h %l %u %t \"%r\" %>s %b \"%{Referer}i\" \"%{User-Agent}i\"
  logHeaders              7
  rollingSize             100M
  keepDays                60
  compressArchive         1
}

index  {
  useServer               1
  autoIndex               0
}

expires  {
  enableExpires           1
}

phpIniOverride  {
php_admin_value error_reporting 22519
php_admin_value error_log /usr/local/lsws/logs/pterodactyl.app-error.log
php_admin_flag log_errors On
php_admin_flag display_errors Off
}

rewrite  {
  enable                  1
  autoLoadHtaccess        1
}
```

# Cloudflare

#### SSL/TLS > SSL/TLS encryption mode
```
Default is Flexible, change to Full
```

#### Origin Server > Origin Certificates

Create new
```
Generate private key and CSR with cloudflare > RSA (2048)
Hostnames: <Leave default>
Certificate Validity: 15 years
```

#### Origin Certificate Installation
```
Paste text from "Origin Certificate" box into new file /usr/local/lsws/conf/cert/key.pem
Paste text from "Private Key" box into new file /usr/local/lsws/conf/cert/cert.pem
```

# Litespeed

#### Server Configuration > External App > lsphp > Actions > Edit
```
Run As User: lsws
Run As Group: lsws
```

#### Virtual Hosts > Add (+)
```
Virtual Host Name: yourdomain.com
Virtual Host Root: /var/www/pterodactyl
Config File: $SERVER_ROOT/conf/vhosts/yourdomain.com.conf
Notes: <Leave default>
Max Keep-Alive Requests: <Leave default>
Follow Symbolic Link: No
Enable Scripts/ExtApps: Yes
Restrained: Yes
External App Set UID Mode: <Leave default>
suEXEC User: lsws
suEXEC Group: lsws
Static Requests/second: <Leave default>
Dynamic Requests/second: <Leave default>
Outbound Bandwidth (bytes/sec): <Leave default>
Inbound Bandwidth (bytes/sec): <Leave default>
```
#### Listeners > Add (+)
```
Listener Name: Default (SSL)
IP Address: ANY IPv4
Port: 443
Binding: <Leave default>
Enable REUSEPORT: <Leave default>
Secure: Yes
Notes: <Leave default>
```
#### Listeners > Default SSL > Actions > View > SSL
```
Private Key File: /usr/local/lsws/conf/cert/key.pem
Certificate File: /usr/local/lsws/conf/cert/cert.pem
```
#### Listeners > Default SSL > Virtual Host Mappings > Add (+)
```
Assign your virtual host(s) and set the domain name(s)
```
#### Listeners > Default > Virtual Host Mappings > Add (+)
```
Assign your virtual host(s) and set the domain name(s)
```
