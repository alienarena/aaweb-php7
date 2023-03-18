/* display occupied servers using bery's api*/


function FpsWS(gameName) {
    this.gameName = gameName;
    this.url = 'wss://api.bery.dev/ws/fps/' + this.gameName + '/';
    this.init();
}

FpsWS.prototype = {
    wrapper: document.querySelector('#occupied-servers'),

    occupied_servers: {},

    init: function() {
        this.connect();
    },

    connect: function() {
        let client = new WebSocket(this.url);

        let SELF = this;

        client.onopen = function() {
            client.send(JSON.stringify({'occupied': true}));
        };

        client.onmessage = function(e) {
            const data = JSON.parse(e.data);

            if(data.occupied) {
                SELF.occupied_servers = {};
                for(let server of data.occupied) {
                    key = server.ip + ":" + server.port;
                    SELF.occupied_servers[key] = server;
                }
            } else if(data.occupation_changed) {
                key = data.occupation_changed.ip + ":" + data.occupation_changed.port;
                if(data.occupation_changed.humans) {
                    SELF.occupied_servers[key] = data.occupation_changed;
                } else {
                    delete SELF.occupied_servers[key];
                }
            }

            if(!Object.keys(SELF.occupied_servers).length) {
                SELF.wrapper.innerHTML = "No online players at the moment.";
                return null;
            }

            let html = "";
            for(let key in SELF.occupied_servers) {
                if(!SELF.occupied_servers.hasOwnProperty(key)) {continue;}
                let server = SELF.occupied_servers[key];
                html += "<div class=\"server\">";
                html += "<h3>" + server.hostname + " (" + server.ip + ":" + server.port + ")</h3>";
                for (let player of server.humans) {
                    html += "<div class=\"player\">";
                    html += player.name + " &nbsp; (score: " + player.score + ", ping: " + player.ping + ")";
                    html += "</div>";
                }
                html += "</div>";
            }
            SELF.wrapper.innerHTML = html;
        };

        client.onclose = function() {
            console.error('Socket closed unexpectedly');
            SELF.client = null;
            console.log('Reconnecting...');
            SELF.reconnect();
        };
    },

    reconnect: function() {
        let SELF = this;
        setTimeout(function() {SELF.connect();}, 1000);
    }
}

new FpsWS('alienarena');
