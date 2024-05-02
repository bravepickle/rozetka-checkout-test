import uuid, random, string
from locust import HttpUser, task, between

class Customer(HttpUser):
    host = 'http://127.0.0.1:8080'

    # wait_time = between(1, 5)

    @task
    def make_purchase(self):
        # authenticate user
        id = uuid.uuid4()
        self.client.post("/auth", json={"username": str(id)})

        # send purchase request
        self.client.post("/purchase", json={
            'delivery': {
                'address': 'some address %s' % id,
                'phone': '+380' + ''.join(random.sample(string.digits, 9))
            },

        })

