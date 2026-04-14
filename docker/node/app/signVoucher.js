import express from 'express';
import { ethers } from 'ethers';
import dotenv from 'dotenv';

dotenv.config();

const app = express();
const port = 3000; // You can choose any port

// Middleware to parse JSON bodies
app.use(express.json());

const privateKey = process.env.SIGNER_PRIVATE_KEY;

async function createVoucher(categoryId, address) {
    let message = (address + categoryId.toString()).toLowerCase();
    const messageHash = ethers.solidityPackedKeccak256(["string"], [message]);
    const signer = new ethers.Wallet(privateKey);

    return await signer.signMessage(ethers.getBytes(messageHash));
}

// Define a POST route
app.post('/create-voucher', async (req, res) => {
    try {
        const { categoryId, userAddress } = req.body;
        if (!categoryId || !userAddress) {
            return res.status(400).send('Missing categoryId or userAddress');
        }

        const signature = await createVoucher(categoryId, userAddress);
        res.json({ signature });
    } catch (error) {
        console.error('Error:', error);
        res.status(500).send('Internal Server Error');
    }
});

app.listen(port, () => {
    console.log(`Server running on http://localhost:${port}`);
});
