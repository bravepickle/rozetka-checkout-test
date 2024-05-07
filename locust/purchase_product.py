import uuid, random, string
from locust import HttpUser, task, between, tag, constant_throughput

POSTMARK_ID = 100

class PostmarkHunter(HttpUser):
    weight = 100  # make this request 100 times more often than the other
    wait_time = constant_throughput(0.1)

    @tag('stream_mode')  # stream mode for handling purchase requests
    @tag('skip_auth')  # do not make auth requests. Only purchase requests
    @task
    def make_purchase_stream(self):
        id = uuid.uuid4()
        # send purchase request
        with self.client.post("/index.php?action=purchase&mode=stream&session_stop=0&skip_auth=1&mark=1",
            data={
                'delivery[address]': 'some address %s' % id,
                'delivery[phone]': '+380' + ''.join(random.sample(string.digits, 9)),
                'delivery[email]': 'user-' + ''.join(random.sample(string.digits + string.ascii_lowercase, 6)) + '@example.com',
                'items[0][product_id]': POSTMARK_ID,
                'items[0][count]': 1,
            },
            catch_response=True
        ) as response:
            if response.status_code == 200:
                response.success()
            else:
                response.failure("[HTTP %d] %s" % (response.status_code, response.text))

    @tag('simple_mode')
    @tag('skip_auth')
    @task
    def make_purchase_simple(self):
        id = uuid.uuid4()
        # send purchase request
        with self.client.post("/index.php?action=purchase&mode=simple&session_stop=0&skip_auth=1&mark=1",
            data={
                'delivery[address]': 'some address %s' % id,
                'delivery[phone]': '+380' + ''.join(random.sample(string.digits, 9)),
                'delivery[email]': 'user-' + ''.join(random.sample(string.digits + string.ascii_lowercase, 6)) + '@example.com',
                'items[0][product_id]': POSTMARK_ID,
                'items[0][count]': 1,
            },
            catch_response=True
        ) as response:
            if response.status_code == 200:
                response.success()
            else:
                response.failure("[HTTP %d] %s" % (response.status_code, response.text))

class RegularCustomer(HttpUser):
    weight = 3
    wait_time = constant_throughput(0.1)

    @tag('stream_mode')
    @tag('skip_auth')
    @task
    def make_purchase_steam(self):
        id = uuid.uuid4()
        # send purchase request
        with self.client.post("/index.php?action=purchase&mode=stream&session_stop=0&skip_auth=1&mark=0",
            data={
                'delivery[address]': 'some address %s' % id,
                'delivery[phone]': '+380' + ''.join(random.sample(string.digits, 9)),
                'delivery[email]': 'user-' + ''.join(random.sample(string.digits + string.ascii_lowercase, 6)) + '@example.com',
                'items[0][product_id]': random.randint(1, 1000000),
                'items[0][count]': 1,
            },
            catch_response=True
        ) as response:
            if response.status_code == 200:
                response.success()
            else:
                response.failure("[HTTP %d] %s" % (response.status_code, response.text))
