require('dotenv').config();
const axios = require('axios');

const phoneNumber = process.argv[2];

if (!phoneNumber) {
    console.error('Please provide a phone number as a command line argument. Ex: node put.js 18675181010');
    process.exit(1);
}

const endpoint = process.env.BASE_URL + `/ims/subscriber/${phoneNumber}`;

axios.delete(endpoint)
    .then(response => {
        console.log('Response Data:', response.data);
    })
    .catch(error => {
        console.error('Error:', error.message);
    });