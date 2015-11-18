=======================================
Requirments
=======================================

- php 5.3
- MySql database(5.5.44). 
- composer
- php cli
- git

=======================================
Installation process 
=======================================

- Open terminal, cd to working direcotry and type: git clone https://github.com/djnikola/lendico.git
- type: cd lendico/.
- type: composer install
- Change credentials for database in app/config/parametars.yml
- Creating database. type: php app/console doctrine:database:create
- Creating database schema: php app/console doctrine:schema:create
- Fill database with data:  php app/console doctrine:fixtures:load
- You can now run service: php app/console server:run


=======================================
Run service
=======================================
- Run service via console. In working directory type: php app/console server:run

=======================================
API documentation 
=======================================

Read API.md file


=======================================
Teting via running tests
=======================================

In working dir type: bin/phpunit -c app 
This qwill run all avaiable tests.

=======================================
Teting via comamnd line
=======================================

--get account for json format
curl -i http://localhost:8000/api/v1/accounts/1.json

--get account for xml format
curl -i http://localhost:8000/api/v1/accounts/1.json

--get all account for json format
curl -i http://localhost:8000/api/v1/accounts.json

--creating new account
curl -X POST -d '{"name":"test","status":"active"}' http://localhost:8000/api/v1/accounts.json --header "Content-Type:application/json" -v

--deactivating account
curl -X PUT http://localhost:8000/api/v1/accounts/3.json --header "Content-Type:application/json" -v

--adding deposit of 2 USD on account id 1
curl -i -X PUT http://localhost:8000/api/v1/accounts/1/amounts/2/currencies/USD/opers/+.json

--Withdraw of 1 USD on account id 1
curl -i -X PUT http://localhost:8000/api/v1/accounts/1/amounts/1/currencies/USD/opers/-.json

--transfer from account id 2 100 USD to account id 2
curl -i -X PUT http://localhost:8000/api/v1/accounts/1/amounts/2/currencies/USD/tos/2.json


=======================================
Q&A section
=======================================

Q: How should monetary values/decimals/fractions be handled?
A: The best thing is to have separate account amount for each currency. That is provided via account_amount table. Decimal values shoudld be based on determing how system accurate should be. 

Q: How should we deal with transactions and history?
A: Transactions are atomic operators that either are executed or not. If not it should return database in previous state (rollback). History of transaction It can be done via triggers on account_amount table and log in separate table accounts changes. 

Q: Architecture: what are the contexts, domains, entities, attributes?
A: Domain is area of a problem. Contexts are different views of some domain. Entities are object that just hold data information. Attributes are properties of entities. 


