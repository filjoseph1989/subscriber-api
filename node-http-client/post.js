require('dotenv').config();
const axios = require('axios');

const randomPhoneNumber = () => {
    const prefix = '1687518';
    const suffix = Math.floor(Math.random() * 9000) + 1000;
    return prefix + suffix;
}

const subscriber = {
    phoneNumber: randomPhoneNumber(),
    username: Math.floor(Math.random() * 100000).toString(), // Random username
    password: "p@ssw0rd!", // Use a static password or generate dynamically if needed
    domain: "ims.mnc660.mcc302.3gppnetwork.org",
    status: "INACTIVE",
    features: {
        callForwardNoReply: {
            provisioned: true,
            destination: "tel:+18675182800"
        }
    }
}

const endpoint = process.env.BASE_URL + '/ims/subscriber';

axios.post(endpoint, subscriber)
    .then(response => {
        console.log('Response Data:', response.data);
    })
    .catch(error => {
        console.error('Error:', error.message);
    });