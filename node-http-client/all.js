require('dotenv').config();

const limit = process.argv[2];
const offset = process.argv[3];

const axios = require('axios');
let endpoint = process.env.BASE_URL + '/ims/subscriber/all';

if (limit != undefined && offset != undefined) {
    endpoint = process.env.BASE_URL + `/ims/subscriber/all/${limit}/${offset}`;
} else {
    if (limit != undefined || offset != undefined) {
        console.log('limit and offset are required')
        process.exit(1);
    }
}

axios.get(endpoint)
    .then(response => {
        console.log('Response Data:', response.data);
    })
    .catch(error => {
        console.error('Error:', error.message);
    });