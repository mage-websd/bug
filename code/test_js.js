var loadJS = function(url, implementationCode, location){
    //url is URL of external file, implementationCode is the code
    //to be called from the file, location is the location to
    //insert the <script> element

    var scriptTag = document.createElement('script');
    scriptTag.src = url;

    scriptTag.onload = implementationCode;
    scriptTag.onreadystatechange = implementationCode;

    location.appendChild(scriptTag);
};
var yourCodeToBeCalled = function(){
//your code goes here
}
loadJS('https://code.jquery.com/jquery-3.4.1.min.js', false, document.body);
let url = 'https://ip-ranges.atlassian.com/';
let port = 80;

let splitCIDR = function(data) {
    let ip = {
        'v4': [],
        'v6': []
    };
    data.forEach(function(item) {
        if (item.cidr.indexOf(':') === -1) {
            ip.v4.push(item.cidr);
        } else {
            ip.v6.push(item.cidr);
        }
    });
    return ip;
}

let execAddSG = function(data) {
    let ip = splitCIDR(data);
    htmlAddRule(ip);
};

let htmlAddRule = function(ip) {
    htmlAddRuleV4(ip.v4);
    htmlAddRuleV4(ip.v6);
}

let htmlAddRuleV4 = function(ipArr) {
    console.log(ipArr);
    if (ipArr.length === 0) {
        return null;
    }
    ipArr.forEach(function(item) {
        jQuery('awsui-button[data-id="add-button"] button').trigger('click');
        let domAdded = $('.simple-attribute-editor.awsui-util-container > div > .awsui-grid > .awsui-row:last');
        /* set port */
        domAdded.find('awsui-input[data-id^="port-"] input').val(port);
        /* set IP */
        let inputIP = domAdded.find('awsui-autosuggest[data-id^="source-"] input');
        let itemTmp = item;
        setTimeout(function() {
            inputIP.val(itemTmp);
        }, 500);
    });
}

jQuery.ajax({
    url: url,
    action: 'get',
    dataType: 'json',
    success: function(data) {
        if (typeof data !== 'object' || typeof data.items !== 'object' || data.items.length === 0) {
            console.log("Not item cidr");
            return null;
        }
        execAddSG(data.items);
    },
});


0: "52.41.219.63/32"
1: "34.216.18.129/32"
2: "13.236.8.128/25"
3: "18.246.31.128/25"
4: "34.236.25.177/32"
5: "185.166.140.0/22"
6: "34.199.54.113/32"
7: "35.155.178.254/32"
8: "52.204.96.37/32"
9: "35.160.177.10/32"
10: "52.203.14.55/32"
11: "18.184.99.128/25"
12: "52.215.192.128/25"
13: "104.192.136.0/21"
14: "18.205.93.0/27"
15: "35.171.175.212/32"
16: "18.136.214.0/25"
17: "52.202.195.162/32"
18: "13.52.5.0/25"
19: "34.218.168.212/32"
20: "18.234.32.128/25"
21: "34.218.156.209/32"
22: "52.54.90.98/32"
23: "34.232.119.183/32"
24: "34.232.25.90/32"




