## This is an example of doing a full Wireguard tunnel between two hosts REMOTELY (once the client turns on tunnel, ALL traffic will be redirected to server!)

SERVER_PUBLIC_KEY			    = The Wireguard public key of the server (This is a pair of private key!!)
SERVER_PRIVATE_KEY			  = The Wireguard private key of the server (This is a pair of public key!!)
CLIENT_PUBLIC_KEY			    = The Wireguard public key of the client (This is a pair of private key!!)
CLIENT_PRIVATE_KEY			  = The Wireguard private key of the client (This is a pair of public key!!)
SERVER_PUBLIC_IP			    = The public IP of your server running Wireguard
SERVER_PORT					      = The port of your Wireguard server
SERVER_NETWORK_INTERFACE	= The public network interface of the Wireguard server
YOUR_HOME_PUBLIC_IP			  = The public IP of where you are connecting to the Wireguard client from
CLIENT_DEFAULT_GATEWAY 		= The default gateway of the Wireguard client
CLIENT_NETWORK_INTERFACE	= The public network interface of the Wireguard client
CLIENT_PUBLIC_IP			    = The public IP of your client running Wireguard

## Install wireguard and dependencies (both machines)
apt install wireguard resolvconf tcpdump net-tools iptables

## Create Wireguard client and server keys (both machines)
cd /etc/wireguard/
wg genkey | tee privatekey | wg pubkey > publickey

## Create Wireguard config file on client (Tunnel client)
vi /etc/wireguard/wg0.conf
[Interface]
PrivateKey = CLIENT_PRIVATE_KEY
Address = 10.60.1.10/24, fd8e:3269:1d5a:aaaa::2/64
DNS = 1.1.1.1

[Peer]
PublicKey = SERVER_PUBLIC_KEY
AllowedIPs = 0.0.0.0/0, ::/0
Endpoint = SERVER_PUBLIC_IP:SERVER_PORT

## Create static route on client in case of tunnel issues (Tunnel client)
ip route add YOUR_HOME_PUBLIC_IP/32 via CLIENT_DEFAULT_GATEWAY dev CLIENT_NETWORK_INTERFACE onlink

## Create Wireguard config file on server (Tunnel server)
vi /etc/wireguard/wg0.conf
[Interface]
Address = 10.60.1.1/24, fd8e:3269:1d5a:aaaa::1/64
ListenPort = SERVER_PORT
PrivateKey = SERVER_PRIVATE_KEY

[Peer]
# Client 1
PublicKey = CLIENT_PUBLIC_KEY
AllowedIPs = 10.60.1.10/32, fd8e:3269:1d5a:aaaa::2/128

## Enable IP forwarding on server (Tunnel server)
sysctl -w net.ipv4.ip_forward=1
sysctl -w net.ipv6.conf.all.forwarding=1
vi /etc/sysctl.conf
Uncomment net.ipv4.ip_forward=1
Uncomment net.ipv6.conf.all.forwarding=1

## Add static routes for IPs to bypass tunnel (YOUR_HOME_PUBLIC_IP is being allowed to connect direct to CLIENT_PUBLIC_IP, bypassing wireguard tunnel)
vi /etc/netplan/01-netcfg.yaml
network:
  version: 2
  renderer: networkd
  ethernets:
    CLIENT_NETWORK_INTERFACE:
      addresses:
        - CLIENT_PUBLIC_IP/32
        - xxxxxxxxxxxxxxxxxx
      routes:
        - on-link: true
          to: 0.0.0.0/0
          via: CLIENT_DEFAULT_GATEWAY
        - on-link: true
          to: YOUR_HOME_PUBLIC_IP/32
          via: CLIENT_DEFAULT_GATEWAY
		  
		  
## Save netplan changes
netplan try
netplan apply

## Set up iptables rules on wireguard server (Tunnel server)
## Allow outbound traffic forwarding for Wireguard network (v4)
/usr/sbin/iptables -A FORWARD -i SERVER_NETWORK_INTERFACE -o wg0 -j ACCEPT
/usr/sbin/iptables -A FORWARD -i wg0 -j ACCEPT
/usr/sbin/iptables -t nat -A POSTROUTING -o SERVER_NETWORK_INTERFACE -j MASQUERADE

## Allow outbound traffic forwarding for Wireguard network (v6)
/usr/sbin/ip6tables -A FORWARD -i SERVER_NETWORK_INTERFACE -o wg0 -j ACCEPT
/usr/sbin/ip6tables -A FORWARD -i wg0 -j ACCEPT
/usr/sbin/ip6tables -t nat -A POSTROUTING -o SERVER_NETWORK_INTERFACE -j MASQUERADE

## Forward traffic for SERVER_PUBLIC_IP port 223/tcp to Wireguard client 10.60.1.10 port 22/tcp
/usr/sbin/iptables -t nat -A PREROUTING -d SERVER_PUBLIC_IP -p tcp --dport 223 -j DNAT --to-destination 10.60.1.10:22

/usr/sbin/iptables -t nat -A PREROUTING -d SERVER_PUBLIC_IP -p tcp --dport 25565:25665 -j DNAT --to-destination 10.60.1.10:25565-25665

## Save Iptables rules and persist on reboot
apt install iptables-persistent
iptables-save >/etc/iptables/rules.v4
ip6tables-save >/etc/iptables/rules.v6

## Start the wireguard service on server (Tunnel server)
systemctl enable --now wg-quick@wg0

## Start the wireguard service on client (Tunnel client)
systemctl start wg-quick@wg0

-- This is where the connection MAY be lost to the internet on client (Tunnel client) --

## Check ping from wireguard client -> wireguard server
ping 10.60.1.1
ping fd8e:3269:1d5a:aaaa::1

-- If pings are OK then the tunnel is established and working, so configuration is correct of both wireguard client and server --

## Start the wireguard service on client to start on boot (Tunnel client)
systemctl enable wg-quick@wg0

## Check external IP from wireguard client (Tunnel client)
curl -4 ifconfig.me

-- If IP returned is SERVER_PUBLIC_IP then the tunnel is functioning between the internet --

## From your own machine, try to SSH to port 223 of server (Tunnel server)
ssh -p 223 root@SERVER_PUBLIC_IP

-- If connection is successful, port forwarding between Tunnel server -> Tunnel client is working --
