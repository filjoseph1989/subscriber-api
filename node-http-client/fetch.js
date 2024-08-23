require('dotenv').config();
const axios = require('axios');

const phoneNumber = process.argv[2];

if (!phoneNumber) {
    console.error('Please provide a phone number as a command line argument. Ex: node fetch.js 18675181010');
    process.exit(1);
}

// Define the endpoint
const url = process.env.BASE_URL + `/ims/subscriber/${phoneNumber}`;

// Send a GET request to the endpoint
axios.get(url)
    .then(response => {
        console.log('Response Data:', response.data);
    })
    .catch(error => {
        console.error('Error:', error.message);
    });