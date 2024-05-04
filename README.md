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
