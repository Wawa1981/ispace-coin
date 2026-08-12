import 'dotenv/config';
import express from 'express';
import { BitGoAPI } from 'bitgo';
import btcPkg from '@bitgo/sdk-coin-btc';
import ethPkg from '@bitgo/sdk-coin-eth';

const { Tbtc, Btc } = btcPkg;
const { Eth, Teth, Hteth } = ethPkg;

const app = express();
app.use(express.json());

const bitgo = new BitGoAPI({ env: 'test' });

bitgo.register('tbtc', Tbtc.createInstance);
bitgo.register('btc', Btc.createInstance);
bitgo.register('eth', Eth.createInstance);

if (Teth) {
  bitgo.register('teth', Teth.createInstance);
}

if (Hteth) {
  bitgo.register('hteth', Hteth.createInstance);
}

bitgo.authenticateWithAccessToken({
  accessToken: process.env.BITGO_ACCESS_TOKEN,
});

app.get('/api/v2/ping', (req, res) => {
  res.json({
    status: 'service is ok!',
    environment: 'Testnet',
  });
});

app.post('/api/v2/:coin/wallet/:walletId/address', async (req, res) => {
  try {
    const { coin, walletId } = req.params;

    const wallet = await bitgo.coin(coin).wallets().get({
      id: walletId,
    });

    const newAddress = await wallet.createAddress({
      label: req.body.label,
    });

    res.json(newAddress);
  } catch (e) {
    console.error(e);

    res.status(500).json({
      error: e.message,
    });
  }
});

const PORT = 3080;

app.listen(PORT, '0.0.0.0', () => {
  console.log(`✅ BitGo local server running on port ${PORT} (testnet)`);
});

// 🪙 Créer un wallet ETH testnet
app.post('/api/v2/create-eth-wallet', async (req, res) => {
  try {
    const coin = bitgo.coin('hteth');

    const wallet = await coin.wallets().generateWallet({
      label: 'iSpaceCoin ETH Test',
      passphrase: process.env.BITGO_WALLET_PASSPHRASE,
      enterprise: process.env.BITGO_ENTERPRISE_ID,
      walletVersion: 6,
      multisigType: 'tss',
      type: 'hot',
    });

    res.json(wallet);
  } catch (e) {
    console.error(e);

    res.status(500).json({
      error: e.message,
    });
  }
});
