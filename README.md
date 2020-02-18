# RabbitMQ PHP Test
## Topic-based Example

* composer install
* docker-compose up -d
* open 3 terminals
* php crm_listener.php & php erp_listener & php sender.php
* sender will send one message, both will react, erp will send a own response an only crm will react


