## Installing custom ISO image to OVH VPS

### 1) Reboot VPS in rescue mode

### 2) Connect to Rescue Mode via SSH, unmount /dev/sdb1 and remove all /dev/sdbX partitions:

```
lsblk
umount /dev/sdb1
fdisk -u /dev/sdb
> d
> d
> w
```

### 3) Install required packages:

```
# apt update
# apt install qemu-kvm
```
### 4) Make a RAM disk for the ISO (in this example we set 2GB):

```
# wget mkdir /mnt/ramdisk
# mount -t tmpfs -o rw,size=2G tmpfs /mnt/ramdisk
```
### 5) Download your ISO:

```
# cd /mnt/ramdisk/
# wget https://espeon.dev/pfSense-CE-2.5.2-RELEASE-amd64.iso
```
### 6) Run qemu (replace ISO location and vdisk name if required):

```
# qemu-system-x86_64 -netdev type=user,id=mynet0 -device virtio-net-pci,netdev=mynet0 -m 2048 -enable-kvm -drive index=0,media=disk,if=virtio,file=/dev/sdb -vga qxl -spice port=5900,addr=127.0.0.1,disable-ticketing -daemonize -cdrom /mnt/ramdisk/CentOS-8.5.2111-x86_64-boot.iso -boot d
```
### 7) If your workstation is running Linux, forward a port through an SSH tunnel to your VPS (for Windows open powershell and run command):

```
# ssh -4 -v root@<your_vps_ip> -L 5900:localhost:5900
```
### 8) On your localhost, connect to SPICE running on local port 5900 (for Windows download https://releases.pagure.org/virt-viewer/virt-viewer-x86-11.0-1.0.msi):

```
# remote-viewer
> spice://127.0.0.1?port=5900
```
### 9) Perform installation as usual.

### 10) Reboot server and exit Rescue Mode.

### 11) Connect to your new instance running your custom OS.
