# Gloo Order Sync #

This a magento module that sync the data of an order with ECMS core when ever an order is updated

###  ###

* Magento module
* Version 1.0.0

### How do I get set up? ###

* Get the folder
* Copy it to magento's app/code folder
* Get into the docker container of magento using docker exec -it php bash - make sure docker-compose is up
* Enter on your terminal sudo php bin/magento setup:upgrade - this will make magento see the module
* Run php bin/magento module:enable Gloo_OrderStatusSync - to enable module
* Run sudo chown app:app -R . - this is for permission 
* check your database to confirm that the table gloo_orderStatusSync_order is present
* You are good to go!
