window.addEventListener("load", init);

let stripe;
let sessionid;

function init() {
    stripe = new Stripe("pk_test_51Grjv6HQXmOPYXA5sbNkiwgHuYi72aVm5j1a94NOfUgj9ygy983K5NweAXTjjmnAl2JMmZUoOYCkt4NVk4NTNRYz00GQRPANEY");
    sessionid = document.getElementById("sessionid").getAttribute("value");
    setTimeout(redirect, 3000);
}

function redirect() {
    stripe.redirectToCheckout({sessionId: sessionid}).then(handler);
}

function handler(result) {
    let para = document.getElementsByTagName("p");
    para = para[0];
    para.innerHTML = para.innerHTML + "<br><br>" + result.error.message;
}