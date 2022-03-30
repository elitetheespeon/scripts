In this example, the Wireguard VPN server public IP is `2.2.2.2` on UDP port `59000`.
The local Wireguard tunnel IP for the server is `10.109.104.1` and the local Wireguard client IP is `10.109.104.2`
So from the Wireguard server `10.109.104.1`, you should be able to ping the Wireguard client `10.109.104.2` when the tunnel is up.

```
root@wg-server:~# cat /etc/wireguard/wg0.conf
[Interface]
Address = 10.109.104.1/24
ListenPort = 59000
PrivateKey = /Your Generated Private Server Key=

[Peer]
# Client 1
PublicKey = /Your Generated Public Client Key=
AllowedIPs = 10.109.104.2/32
```

```
root@wg-client:/etc/wireguard# cat /etc/wireguard/wg0.conf
[Interface]
PrivateKey = /Your Generated Private Client Key=
Address = 10.109.104.2/32
DNS = 1.1.1.1, 2606:4700:4700::1111

[Peer]
# Server
PublicKey = /Your Generated Public Server Key=
AllowedIPs = 0.0.0.0/0, ::/0
Endpoint = 2.2.2.2:59000
```
