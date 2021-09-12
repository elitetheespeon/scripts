## Set outbound IP to second IP
/sbin/iptables -t nat -A POSTROUTING -o eth0 -j SNAT --to 2.2.2.2
## Allow outbound traffic forwarding for Wireguard network
/sbin/iptables -A FORWARD -i eth0 -o wg0 -j ACCEPT
/sbin/iptables -A FORWARD -i wg0 -j ACCEPT
/sbin/iptables -t nat -A POSTROUTING -o eth0 -j MASQUERADE
## Forward traffic for 2.2.2.2 SSH to Wireguard client 10.109.144.2
/sbin/iptables -t nat -A PREROUTING -d 2.2.2.2 -p tcp --dport 22 -j DNAT --to-destination 10.109.144.2

## Where <2.2.2.2> would be public VPN IP and <10.109.144.2> would be private Wireguard client IP to direct traffic to.
## In this example, traffic coming in for IP 2.2.2.2 on TCP port 22 would be directed to the Wireguard client at 10.109.144.2.
## Which results in SSH for the server connected to the Wireguard tunnel to be accessed from IP 2.2.2.2.
