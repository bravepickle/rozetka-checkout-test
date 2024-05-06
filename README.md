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
4. Run tests (each command in separate terminal)
   ```shell
   # reset DBs
   docker compose exec php sh -c 'composer run app:reset'
   # start worker
   docker compose exec php composer run app:process_orders
   # start locust UI performance testing suite
   locust -f purchase_product.py --host http://localhost:8181 \
      --tags stream_mode skip_auth --users 5500 -t 2m -r 3000 PostmarkHunter
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
- [ ] regular vs postmark hunter
- [ ] PHP message: PHP Warning:  session_start(): Failed to read session data: redis (path: tcp://redis:6379) in /app/src/Service/Application.php on line 43
- [ ] retry session_start try-catch throwable
- [ ] items_count can be negative values - needs compensation operation - due to concurrent requests + log event
- [ ] items_count = items_count - 1 is good but can provide problems without compensation
- [ ] event sourcing streams fix somewhat problem with compensation - less workers than open connections and predictable, durable
- [ ] ignore_user_abort() - is important
- [ ] increase inventory postmark for comparison
- [ ] RAM must be monitored closely
- [ ] mysql transactions are important
- [ ] Redis have various eviction policies when maxmemory-policy is reached
- [ ] session_write_close, db, redis connections, close asap call ASAP
- [ ] separate Redis DB with sessions and policy on exhaustion
- [ ] Redis socket issue - server went away
- [ ] Redis disable persistance to file save ""
- [ ] use Redis on host machine instead
- [ ] docker network issues - redis server has gone away even though it works
- [ ] docker compose issues - stuck db, redis, php containers
- [ ] virtualized env is bad idea for testing high load
- [ ] push from redis to RDBMS all counter values 
- [ ] networking interfaces & docker can be stuck
- [ ] simultaneous writes to single record


- [ ] 

