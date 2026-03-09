# YoutubeTS API Tester

A simple, single-file PHP script to test the [YoutubeTS](https://youtubets.com) Transcript API. No database, no framework, no dependencies — just PHP + cURL.

![PHP](https://img.shields.io/badge/PHP-8.0+-blue) ![License](https://img.shields.io/badge/License-MIT-green)

## Features

- 📄 **Extract transcripts** from any YouTube video (URL or Video ID)
- 📊 **Check API usage** — see credits used/remaining
- 💰 **View plans** — see all available API plans
- 💚 **Health check** — verify API is online
- 🎨 Clean dark UI, mobile-friendly
- 🔑 Just set your API key and go

## Quick Start

1. **Clone this repo:**
   ```bash
   git clone https://github.com/YOUR_USERNAME/youtubets-api-tester.git
   cd youtubets-api-tester
   ```

2. **Set your API credentials** — edit `index.php` line 17-18:
   ```php
   $config = [
       'api_key'    => 'ytts_YOUR_KEY_HERE',
       'api_secret' => 'YOUR_SECRET_HERE',
       'base_url'   => 'https://youtubets.com/api/v1',
   ];
   ```

3. **Run with PHP built-in server:**
   ```bash
   php -S localhost:8000
   ```

4. **Open** http://localhost:8000 in your browser.

## Get Your API Key

1. Register at [youtubets.com](https://youtubets.com)
2. Go to **API Dashboard**
3. Generate your API key (save the secret — it's only shown once!)

## Requirements

- PHP 8.0+
- cURL extension enabled

## API Endpoints Tested

| Endpoint | Method | Auth | Description |
|---|---|---|---|
| `/api/v1/transcript` | POST | ✅ | Extract YouTube transcript |
| `/api/v1/usage` | GET | ✅ | Check your API usage |
| `/api/v1/plans` | GET | ✅ | View available plans |
| `/api/v1/health` | GET | ❌ | API health check |

## License

MIT — use it however you want.

## Links

- 🌐 [YoutubeTS Website](https://youtubets.com)
- 📖 [API Documentation](https://youtubets.com/api/v1/info)
