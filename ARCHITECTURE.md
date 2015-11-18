=======================================
ARCHITECTURE documentation.
=======================================

Project is done in Symfony 2 (Symfony version 2.7.6) & MySql database(5.5.44). 
Manipulation with Account is done via AccountBundle. It contents controller with defined actions. 
Goal is to keep account controller as thiny as posible. Main logig is done in Account handler. 
Account hadle implement Account Handler Interface, which is responsible for all logic.
Implementaton of Account Handler Interface (Account Handler), is injected (Dependecy Injection) via service configuration in AccountBundle/Resources/config/service.xml file.



