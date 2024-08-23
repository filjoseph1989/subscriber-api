## Node Command and Responses

Before running command below make sure you have pnpm install in your system. Then

```bash
pnpm install
```

### 1. Running `node all.js`

When running the command `node all.js`, the response is:

```json
Response Data: [
  {
    id: 3,
    phone_number: "16875189451",
    username: "97287",
    password: "",
    domain: "ims.mnc660.mcc302.3gppnetwork.org",
    status: "INACTIVE",
    features: { callForwardNoReply: [Object] },
    links: {
      prev: "/ims/subscriber/all/100/0",
      next: "/ims/subscriber/all/100/100"
    }
  }
]
```

### 2. Running `node all.js` with a Single Argument

If you run the command `node all.js 10`, the response will be:

```json
Error: limit and offset are required
```

### 3. Running `node all.js` with Two Arguments

If you run the command `node all.js 10 10` to represent `limit` and `offset`, the response will be:

```json
Response Data: [
  {
    id: 3,
    phone_number: "16875189451",
    username: "97287",
    password: "",
    domain: "ims.mnc660.mcc302.3gppnetwork.org",
    status: "INACTIVE",
    features: { callForwardNoReply: [Object] },
    links: {
      prev: "/ims/subscriber/all/10/0",
      next: "/ims/subscriber/all/10/20"
    }
  }
]
```

### 4. Running `node post.js`

Running the command `node post.js` will result in the following response:

```json
Response Data: {
  message: "Subscriber added ID: 7, Phone Number: 16875183572",
  id: "7",
  phoneNumber: "16875183572"
}
```

### 5. Running `node fetch.js <phone_number>`

Example: Running `node fetch.js 16875183572` will result in:

```json
Response Data: {
  id: 7,
  phone_number: "16875183572",
  username: "53628",
  password: "",
  domain: "ims.mnc660.mcc302.3gppnetwork.org",
  status: "INACTIVE",
  features: {
    callForwardNoReply: { destination: "tel:+18675182800", provisioned: true }
  }
}
```

### 6. Running `node delete.js <phone_number>`

#### a. Phone Number Does Not Exist

Example: Running `node delete.js 168751835721111`, where `168751835721111` does not exist, will result in:

```json
Error: Request failed with status code 404
```

#### b. Phone Number Exists

Example: Running `node delete.js 16875183572`, where `16875183572` exists, will result in:

```json
Response Data: { message: "Subscriber 16875183572 deleted" }
```

### 7. Running `node put.js <phone_number>`

#### a. Phone Number Exists

Example: Running `node put.js 16875183572`, where `16875183572` exists, will result in:

```json
Response Data: { message: "Subscriber updated" }
```

(Note: The data will randomly change in the background.)

#### b. Phone Number Does Not Exist

Example: Running `node put.js 16875183572`, where `16875183572` does not exist, will result in:

```json
Error: Request failed with status code 404
```