--- 
customlog: 
  - 
    format: combined
    target: /usr/local/apache/domlogs/njphonefix.com
  - 
    format: "\"%{%s}t %I .\\n%{%s}t %O .\""
    target: /usr/local/apache/domlogs/njphonefix.com-bytes_log
documentroot: /home/njphone/public_html
group: njphone
hascgi: 1
homedir: /home/njphone
ifmodulemodsuphpc: 
  group: njphone
ifmodulemoduserdirc: {}

ip: 206.225.83.170
owner: root
phpopenbasedirprotect: 1
port: 80
scriptalias: 
  - 
    path: /home/njphone/public_html/cgi-bin
    url: /cgi-bin/
  - 
    path: /home/njphone/public_html/cgi-bin/
    url: /cgi-bin/
serveradmin: webmaster@njphonefix.com
serveralias: www.njphonefix.com
servername: njphonefix.com
ssl: 1
usecanonicalname: 'Off'
user: njphone
userdirprotect: ''
