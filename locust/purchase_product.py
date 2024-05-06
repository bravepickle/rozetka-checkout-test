import uuid, random, string
from locust import HttpUser, task, between, tag

POSTMARK_ID = 100

class PostmarkHunter(HttpUser):
    weight = 100  # make this request 100 times more often than the other
    #     wait_time = between(4, 5)

#     wait_time = between(1, 2)
    wait_time = between(4, 5)

    @tag('stream_mode')  # stream mode for handling purchase requests
    @tag('skip_auth')  # do not make auth requests. Only purchase requests
    @task
    def make_purchase_v3(self):
        # authenticate user
        id = uuid.uuid4()
        # send purchase request
        with self.client.post("/index.php?action=purchase&mode=stream&session_stop=0&skip_auth=1&mark=1",
#         with self.client.post("/index.php?action=purchase&mode=stream&session_stop=1&skip_auth=1&mark=1",
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
        # authenticate user
        id = uuid.uuid4()
        # send purchase request
        with self.client.post("/index.php?action=simple&mode=stream&session_stop=0&skip_auth=1&mark=1",
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
