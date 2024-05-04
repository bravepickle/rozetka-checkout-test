import uuid, random, string
from locust import HttpUser, task, between, tag

class PostmarkHunter(HttpUser):
    host = 'http://127.0.0.1:8080'

#     wait_time = 5
#     wait_time = between(1, 5)
    wait_time = between(4, 5)

    @tag('postmark_user') # all tasks for postmark hunter
    @tag('simple_mode') # simple mode for handling purchase requests
    @tag('session_stop') # simple mode for handling purchase requests
    @task(100)
    def make_purchase(self):
        # authenticate user
        id = uuid.uuid4()
        self.client.post("/index.php?action=auth", data={"username": str(id)})

        # send purchase request
        self.client.post("/index.php?action=purchase&mode=simple&session_stop=1", data={
            'delivery[address]': 'some address %s' % id,
            'delivery[phone]': '+380' + ''.join(random.sample(string.digits, 9)),
            'delivery[email]': 'user-' + ''.join(random.sample(string.digits + string.ascii_lowercase, 6)) + '@example.com',
            'items[0][product_id]': 100,
            'items[0][count]': 1,
        })

class RegularCustomer(HttpUser):
    host = 'http://127.0.0.1:8080'

#     wait_time = 5
#     wait_time = between(1, 5)
    wait_time = between(4, 5)

    @tag('regular_user')
    @tag('simple_mode')
    @tag('session_stop')
    @task
    def make_purchase(self):
        # authenticate user
        id = uuid.uuid4()
        self.client.post("/index.php?action=auth", data={"username": str(id)})

        # send purchase request
        self.client.post("/index.php?action=purchase&mode=simple&session_stop=1", data={
            'delivery[address]': 'some address %s' % id,
            'delivery[phone]': '+380' + ''.join(random.sample(string.digits, 9)),
            'delivery[email]': 'user-' + ''.join(random.sample(string.digits + string.ascii_lowercase, 6)) + '@example.com',
            'items[0][product_id]': random.randint(1, 1000000),
            'items[0][count]': 1,
        })
