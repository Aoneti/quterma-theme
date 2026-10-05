import express from 'express';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const app = express();
const PORT = process.env.PORT || 3000;
const HOST = '0.0.0.0';

// Serve static assets and html files with clean URLs
app.use(express.static(__dirname, {
  extensions: ['html', 'htm']
}));

// Route to download WordPress Theme zip
app.get('/quterma.zip', (req, res) => {
  res.download(path.join(__dirname, 'quterma.zip'), 'quterma.zip');
});

// Route for search with query params
app.get('/search', (req, res) => {
  res.sendFile(path.join(__dirname, 'search.html'));
});

// 404 handler
app.use((req, res) => {
  res.status(404).sendFile(path.join(__dirname, '404.html'));
});

app.listen(PORT, HOST, () => {
  console.log(`Кутерьма server running on http://${HOST}:${PORT}`);
});
