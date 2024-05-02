import uuid, random, string
from locust import HttpUser, task, between

class Customer(HttpUser):
    host = 'http://127.0.0.1:8080'

#     wait_time = 5
#     wait_time = between(1, 5)
    wait_time = between(4, 5)

    @task
    def make_purchase(self):
        # authenticate user
        id = uuid.uuid4()
        self.client.post("/index.php?action=auth", data={"username": str(id)})

        # send purchase request
        self.client.post("/index.php?action=purchase", data={
            'delivery[address]': 'some address %s' % id,
            'delivery[phone]': '+380' + ''.join(random.sample(string.digits, 9)),
            'delivery[email]': 'user' + ''.join(random.sample(string.digits + string.ascii_lowercase, 6)) + '@example.com',
            'items[0][product_id]': 100,
            'items[0][count]': 1,
        })
