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
   $ docker compose pull
   $ docker compose build
   $ docker compose up -d
   ```


## TODOs
- [ ] Cleanup Redis DB
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
- [ ] Research and fix NOTICE: PHP message: PHP Warning:  session_start(): Failed to read session data: redis (path: tcp://redis:6379) in /app/src/Service/Application.php on line 18
