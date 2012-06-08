#!/usr/bin/env python
#
# Copyright 2009 Facebook
#
# Licensed under the Apache License, Version 2.0 (the "License"); you may
# not use this file except in compliance with the License. You may obtain
# a copy of the License at
#
#     http://www.apache.org/licenses/LICENSE-2.0
#
# Unless required by applicable law or agreed to in writing, software
# distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
# WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
# License for the specific language governing permissions and limitations
# under the License.
"""Simplified chat demo for websockets.

Authentication, error handling, etc are left as an exercise for the reader :)
"""

import logging
import tornado.escape
import tornado.ioloop
import tornado.options
import tornado.template
import tornado.web
import tornado.websocket
import os.path
import uuid

from tornado.options import define, options

define("port", default=8888, help="run on the given port", type=int)


class Application(tornado.web.Application):
    def __init__(self):

        plugins_path = os.path.join(os.path.dirname(os.path.dirname(__file__)), "plugins")

        handlers = [
            (r"/", MainHandler),
            (r"/chatsocket", ClientSocketHandler),
            (r"/plugin/(.*).js", PluginClientHandler, {"path": plugins_path}),
            (r"/update/(.*)", UpdateHandler)
        ]

        settings = dict(
            cookie_secret="43oETzKXQAGaYdkL5gEmGeJJFuYh7EQnp2XdTP1o/Vo=",
            template_path=os.path.join(os.path.dirname(__file__), "templates"),
            static_path=os.path.join(os.path.dirname(__file__), "static"),
            xsrf_cookies=False,
            autoescape=None,
        )
        tornado.web.Application.__init__(self, handlers, **settings)


class MainHandler(tornado.web.RequestHandler):
    def get(self):
        self.render("index.html", messages=ClientSocketHandler.cache, plugin_clients=plugin_clients)


class PluginClientHandler(tornado.web.StaticFileHandler):
    def parse_url_path(self, url_path):
        return '%s/client.js' % url_path


class UpdateHandler(tornado.web.RequestHandler):
    def post(self, plugin):
        data = self.get_arguments('data')

        items = []
        for item in data:
           ClientSocketHandler.update_cache(plugin, item)

        ClientSocketHandler.send_updates(plugin, data)


class ClientSocketHandler(tornado.websocket.WebSocketHandler):
    clients = set()
    cache = {}
    cache_size = 20

    def allow_draft76(self):
        # for iOS 5.0 Safari
        return True

    def open(self):
        ClientSocketHandler.clients.add(self)
        # Send through any cached data
        ClientSocketHandler.push_cache(self)

    def on_close(self):
        ClientSocketHandler.clients.remove(self)

    @classmethod
    def update_cache(cls, plugin, data):
        if plugin not in cls.cache:
            cls.cache[plugin] = []

        cls.cache[plugin].append(data)
        if len(cls.cache[plugin]) > cls.cache_size:
            cls.cache[plugin] = cls.cache[plugin][-cls.cache_size:]

    @classmethod
    def push_cache(cls, client):
        if not len(cls.cache):
            return

        for plugin in cls.cache:
            if not len(cls.cache[plugin]):
                continue

            message = ClientSocketHandler.generate_message(plugin, cls.cache[plugin])
            try:
                client.write_message(message)
            except:
                logging.error("Error sending message", exc_info=True)

    @classmethod
    def send_updates(cls, plugin, data):
        logging.info("sending message to %d clients", len(cls.clients))
        for client in cls.clients:
            message = ClientSocketHandler.generate_message(plugin, data)
            try:
                client.write_message(message)
            except:
                logging.error("Error sending message", exc_info=True)


    @classmethod
    def generate_message(cls, plugin, data):
        # Check if we are getting multiple data
        if isinstance(data, list):
            data = '['+','.join(data)+']'

        data = {
            "plugin": plugin,
            "body": data
        }

        t = tornado.template.Template('<div class="incoming-data" data-plugin="{{ plugin }}">{{ data }}</div>')
        data['html'] = t.generate(plugin=plugin, data=data["body"])
        return data



def plugin_clients():
    clients = []

    plugins_path = os.path.join(os.path.dirname(os.path.dirname(__file__)), "plugins")
    plugins = os.listdir(plugins_path)
    for plugin in plugins:
        pluginpath = os.path.join(plugins_path, plugin)
        if not os.path.isdir(pluginpath):
            continue

        if os.path.exists(os.path.join(pluginpath, "client.js")):
            clients.append(plugin)

    output = ''
    for client in clients:
        output += '<script src="/plugin/%s.js" type="text/javascript"></script>\n' % client

    return output


def main():
    tornado.options.parse_command_line()
    app = Application()
    app.listen(options.port)
    tornado.ioloop.IOLoop.instance().start()


if __name__ == "__main__":
    main()
