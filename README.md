# rozetka-checkout-test
PoC test task to demonstrate high load processing clients checkout

# Setup
1. Install Docker, Docker Compose, Python 3 with venv module
2. Install [Locust](https://github.com/locustio/locust) tool for load testing, e.g. `pip3 install locust` 
   or using venv:
   ```shell
   cd locust
   python3 -m venv venv
   source source venv/bin/activate
   pip install -r requirements.txt
   
   # alternative - without venv. Installs dependencies globally
   pip install -r requirements.txt
   ```
3. Setup Docker environment
   ```shell
   $ docker compose up -d
   $ docker compose  exec php composer install 
   ```


## TODOs
- [X] Cleanup Redis DB
- [ ] Is it problem with simultaneous reads and writes to same record for product?
- [ ] Redis Steam customer groups with ACK
- [ ] When batch process - 100 records per batch - sum up all products and do single update per product_id - grouping
- [ ] Use event sourcing
- [ ] Use materialized views or counters copy to Redis DB and sync from time to time to DBMS
- [ ] Use decrement instead of reading count and writing it
- [ ] Use task weights in Locust to make race conditions and random results between requests to postmark and other purchases
- [ ] Diagram for processing requests?
- [ ] 500MB RAM takes loading products from RDBMS to Redis DB
- [ ] recover missing products in Redis by checking RDBMS
- [ ] Close session and remove info after processing purchase
- [ ] php session config review and update
- [ ] use Redis for caching sessions
- [ ] use Locust tags to switch strategies for handling requests for comparison
- [ ] fsync on read/write RDBMS, cookies, sessions
- [ ] review all todos and fixmes in code
- [ ] disable nginx access logs?
- [ ] kafka, rabbitmq as alternative
- [ ] 2mln new unique users per hour thats a lot. Need partitioning and clusters
- [ ] exhausted list - show 418 status code to differ from other
- [ ] show added orders, counters offset, reset dbs

