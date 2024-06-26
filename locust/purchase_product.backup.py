import uuid, random, string
from locust import HttpUser, task, between, tag

POSTMARK_ID = 100


class PostmarkHunter(HttpUser):
    weight = 100  # make this request 100 times more often than the other
    #     host = 'http://127.0.0.1:8181'

    #     wait_time = 5
    #     wait_time = between(1, 5)
    #     wait_time = between(4, 5)

    wait_time = between(4, 5)

    #     @tag('simple_mode') # simple mode for handling purchase requests
    #     @tag('session_stop') # simple mode for handling purchase requests
    #     @task
    #     def make_purchase_v1(self):
    #         # authenticate user
    #         id = uuid.uuid4()
    #         self.client.post("/index.php?action=auth", data={"username": str(id)})
    #
    #         # send purchase request
    #         self.client.post("/index.php?action=purchase&mode=simple&session_stop=1&mark=1", data={
    #             'delivery[address]': 'some address %s' % id,
    #             'delivery[phone]': '+380' + ''.join(random.sample(string.digits, 9)),
    #             'delivery[email]': 'user-' + ''.join(random.sample(string.digits + string.ascii_lowercase, 6)) + '@example.com',
    #             'items[0][product_id]': POSTMARK_ID,
    #             'items[0][count]': 1,
    #         })
    #
    #     @tag('simple_mode') # simple mode for handling purchase requests
    #     @tag('session_stop') # simple mode for handling purchase requests
    #     @tag('skip_auth') # do not make auth requests. Only purchase requests
    #     @task
    #     def make_purchase_v2(self):
    #         # authenticate user
    #         id = uuid.uuid4()
    #         # send purchase request
    #         self.client.post("/index.php?action=purchase&mode=simple&session_stop=1&skip_auth=1&mark=1", data={
    #             'delivery[address]': 'some address %s' % id,
    #             'delivery[phone]': '+380' + ''.join(random.sample(string.digits, 9)),
    #             'delivery[email]': 'user-' + ''.join(random.sample(string.digits + string.ascii_lowercase, 6)) + '@example.com',
    #             'items[0][product_id]': POSTMARK_ID,
    #             'items[0][count]': 1,
    #         })

    @tag('stream_mode')  # stream mode for handling purchase requests
    @tag('skip_auth')  # do not make auth requests. Only purchase requests
    @task
    def make_purchase_v3(self):
        # authenticate user
        id = uuid.uuid4()
        # send purchase request
        # self.client.post("/index.php?action=purchase&mode=stream&session_stop=0&skip_auth=1&mark=1", data={
        #     'delivery[address]': 'some address %s' % id,
        #     'delivery[phone]': '+380' + ''.join(random.sample(string.digits, 9)),
        #     'delivery[email]': 'user-' + ''.join(random.sample(string.digits + string.ascii_lowercase, 6)) + '@example.com',
        #     'items[0][product_id]': POSTMARK_ID,
        #     'items[0][count]': 1,
        # })

        with self.client.post("/index.php?action=purchase&mode=stream&session_stop=0&skip_auth=1&mark=1", data={
            'delivery[address]': 'some address %s' % id,
            'delivery[phone]': '+380' + ''.join(random.sample(string.digits, 9)),
            'delivery[email]': 'user-' + ''.join(
                random.sample(string.digits + string.ascii_lowercase, 6)) + '@example.com',
            'items[0][product_id]': POSTMARK_ID,
            'items[0][count]': 1,
        }) as response:
            if response.text != "Success":
                response.failure(exc="Got wrong response")
            else:
                response.failure("SAAAD: %s" % response.text)

# class RegularCustomer(HttpUser):
#     weight = 1
# #     host = 'http://127.0.0.1:8181'
#
# #     wait_time = 5
# #     wait_time = between(1, 5)
# #     wait_time = between(4, 5)
#
#     @tag('simple_mode')
#     @tag('session_stop')
#     @task
#     def make_purchase_v1(self):
#         # authenticate user
#         id = uuid.uuid4()
#         self.client.post("/index.php?action=auth", data={"username": str(id)})
#
#         # send purchase request
#         self.client.post("/index.php?action=purchase&mode=simple&session_stop=1&mark=0", data={
#             'delivery[address]': 'some address %s' % id,
#             'delivery[phone]': '+380' + ''.join(random.sample(string.digits, 9)),
#             'delivery[email]': 'user-' + ''.join(random.sample(string.digits + string.ascii_lowercase, 6)) + '@example.com',
#             'items[0][product_id]': random.randint(1, 1000000),
#             'items[0][count]': 1,
#         })
#
#     @tag('simple_mode')
#     @tag('session_stop')
#     @tag('skip_auth')
#     @task
#     def make_purchase_v2(self):
#         # authenticate user
#         id = uuid.uuid4()
#         self.client.post("/index.php?action=auth", data={"username": str(id)})
#
#         # send purchase request
#         self.client.post("/index.php?action=purchase&mode=simple&session_stop=1&skip_auth=1&mark=0", data={
#             'delivery[address]': 'some address %s' % id,
#             'delivery[phone]': '+380' + ''.join(random.sample(string.digits, 9)),
#             'delivery[email]': 'user-' + ''.join(random.sample(string.digits + string.ascii_lowercase, 6)) + '@example.com',
#             'items[0][product_id]': random.randint(1, 1000000),
#             'items[0][count]': 1,
#         })
#
#     @tag('stream_mode')
#     @tag('skip_auth')
#     @task
#     def make_purchase_v3(self):
#         # authenticate user
#         id = uuid.uuid4()
#         self.client.post("/index.php?action=auth", data={"username": str(id)})
#
#         # send purchase request
#         self.client.post("/index.php?action=purchase&mode=stream&session_stop=0&skip_auth=1&mark=0", data={
#             'delivery[address]': 'some address %s' % id,
#             'delivery[phone]': '+380' + ''.join(random.sample(string.digits, 9)),
#             'delivery[email]': 'user-' + ''.join(random.sample(string.digits + string.ascii_lowercase, 6)) + '@example.com',
#             'items[0][product_id]': random.randint(1, 1000000),
#             'items[0][count]': 1,
#         })
