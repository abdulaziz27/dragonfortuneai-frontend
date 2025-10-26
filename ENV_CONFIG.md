# Environment Configuration

## Add to .env file:

```env
# API Configuration
API_BASE_URL=https://test.dragonfortune.ai
SPOT_MICROSTRUCTURE_API_URL=http://localhost:8000

# Spot microstructure provider toggles
SPOT_SSL_VERIFY=false
SPOT_STUB_DATA=true
```

## Explanation:

- `API_BASE_URL`: Untuk fitur lain (funding rate, open interest, dll) tetap pakai test.dragonfortune.ai
- `SPOT_MICROSTRUCTURE_API_URL`: Khusus untuk spot microstructure pakai localhost:8000 (CoinGlass API)
- `SPOT_STUB_DATA=true`: Menggunakan stub data untuk development

## Result:

- ✅ Spot microstructure → localhost:8000 (CoinGlass API)
- ✅ Fitur lain → test.dragonfortune.ai (existing)
- ✅ No conflict between different APIs
