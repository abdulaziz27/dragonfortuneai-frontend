$key='jED5yIBUPyzpeRTodjcSPGiltvvdAaJQmV1op1ED3v4UkDorgm6O20rRTq3yKWloyebmxw'
try {
    $response = Invoke-WebRequest -Uri 'https://api.cryptoquant.com/v1/btc/market-data/price-ohlcv?window=minute&market=spot&exchange=binance&symbol=btc_usdt&limit=10' -Headers @{Authorization="Bearer $key"; Accept='application/json'}
    $response.Content | Out-File __cq.json
} catch {
    $_ | Out-File __cq.err
}
