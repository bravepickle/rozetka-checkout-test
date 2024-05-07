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
   locust -f purchase_product.py --host http://localhost:8181 --tags stream_mode skip_auth --users 5500 -t 2m -r 3000
   ```

## TODOs
- [X] Cleanup Redis DB
- [x] Is it problem with simultaneous reads and writes to same record for product?
- [x] Redis Steam customer groups with `ACK`
- [x] Use event sourcing
- [x] Use materialized views or counters copy to Redis DB and sync from time to time to DBMS
- [x] Use decrement instead of reading count and writing it
- [x] Use task weights in Locust to make race conditions and random results between requests to postmark and other purchases
- [ ] Recover missing products in Redis by checking RDBMS
- [x] Close session and remove info after processing purchase
- [x] PHP session config review and update
- [x] use Redis for caching sessions
- [x] use Locust tags to switch strategies for handling requests for comparison
- [x] fsync on read/write RDBMS, cookies, sessions
- [ ] Disable nginx access logs
- [ ] Kafka, RabbitMQ as alternative
- [x] Exhausted list - show 418 status code to differ from other
- [x] show added orders, counters offset, reset dbs
- [x] PHP message: `PHP Warning:  session_start(): Failed to read session data: redis (path: tcp://redis:6379) 
      in /app/src/Service/Application.php on line 43`
- [x] retry session_start try-catch throwable
- [x] `items_count` can be negative values - needs compensation operation - due to concurrent requests + log event
- [x] `ignore_user_abort()` - is important
- [x] increase inventory postmark for comparison
- [x] MySQL transactions are important
- [x] Redis have various eviction policies when `maxmemory` is reached
- [x] Session_write_close, db, redis connections, close asap call ASAP
- [ ] Separate Redis DB with sessions and policy on exhaustion
- [x] Redis socket issue - server went away
- [x] Redis disable persistence to file - `save ""`
- [x] Use Redis on host machine instead
- [x] Docker network issues - redis server has gone away even though it works
- [x] Docker compose issues - stuck db, redis, php containers
- [ ] Push from Redis to RDBMS all counter values
- [ ] Add cronjob to sync from redis to db and visa versa

