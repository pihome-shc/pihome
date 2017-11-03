command = "/usr/bin/sudo /sbin/shutdown -h now"
import subprocess
process = subprocess.Popen(command.split(), stdout=subprocess.PIPE)
output = process.communicate()[0]
print output