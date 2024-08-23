require('dotenv').config();
const axios = require('axios');

const phoneNumber = process.argv[2];

if (!phoneNumber) {
    console.error('Please provide a phone number as a command line argument. Ex: node put.js 18675181010');
    process.exit(1);
}

const subscriber = {
    phoneNumber: phoneNumber,
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

axios.put(endpoint, subscriber)
    .then(response => {
        console.log('Response Data:', response.data);
    })
    .catch(error => {
        console.error('Error:', error.message);
    });