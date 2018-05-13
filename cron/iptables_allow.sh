#!/bin/bash
### BEGIN INIT INFO
# Provides:          PiHome
# Required-Start:    $remote_fs $syslog
# Required-Stop:     $remote_fs $syslog
# Default-Start:     2 3 4 5
# Default-Stop:      0 1 6
# Short-Description: OpenVPN web connection
# Description:       This Script allow openvpn traffic to go to internet
#                    placed in /etc/init.d.
### END INIT INFO

# ../../var/www/cron/iptables_allow.sh
# Create link in /etc/init.d
# sudo /etc/init.d/openvpn restart
# sudo /sbin/iptables 

# iptalbes for mysql access from remote server. 
iptables -A INPUT -p tcp -m tcp --dport 3306 -j ACCEPT
iptables -A INPUT -i wlan0 -s 192.168.99.10 -p tcp --destination-port 3306 -j ACCEPT
sudo iptables-save

# Allow incoming SSH
iptables -A INPUT -p tcp --dport 22 -m state --state NEW -s 0.0.0.0/0 -j ACCEPT

# Allow traffic on the TUN interface.
iptables -A INPUT -i tun+ -j ACCEPT
iptables -A FORWARD -i tun+ -j ACCEPT
iptables -A OUTPUT -o tun+ -j ACCEPT

# Allow UDP traffic on port 1194.
iptables -A INPUT -i wlan0 -m state --state NEW -p udp --dport 1194 -j ACCEPT

# NAT the VPN client traffic to the internet
iptables --table nat -A POSTROUTING -o wlan0 -j MASQUERADE
iptables --table nat -A POSTROUTING -o wlan0 -j MASQUERADE
iptables -t nat -A INPUT -i wlan0 -p udp -m udp --dport 1194 -j ACCEPT


# Allow TUN interface connections to be forwarded through other interfaces
iptables -P FORWARD ACCEPT
iptables -A FORWARD -i tun+ -o wlan0 -m state --state RELATED,ESTABLISHED -j ACCEPT
iptables -A FORWARD -i wlan0 -o tun+ -m state --state RELATED,ESTABLISHED -j ACCEPT


# Log any packets which don't fit the rules above...
# (optional but useful)
# iptables -A INPUT -m limit --limit 3/min -j LOG --log-prefix "iptables_INPUT_denied: " --log-level 4
# iptables -A FORWARD -m limit --limit 3/min -j LOG --log-prefix "iptables_FORWARD_denied: " --log-level 4
# iptables -A OUTPUT -m limit --limit 3/min -j LOG --log-prefix "iptables_OUTPUT_denied: " --log-level 4