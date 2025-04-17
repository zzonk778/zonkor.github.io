const express = require('express');
const multer = require('multer');
const path = require('path');
const fs = require('fs-extra');
const AdmZip = require('adm-zip');
const mime = require('mime-types');

const app = express();
const port = 3000;

// Set up EJS templating
app.set('view engine', 'ejs');
app.set('views', path.join(__dirname, 'views'));

// Configure Multer for file uploads
const upload = multer({
    dest: 'uploads/',
    fileFilter: (req, file, cb) => {
        const ext = path.extname(file.originalname).toLowerCase();
        if (ext !== '.zip') {
            return cb(new Error('Only ZIP files are allowed'));
        }
        cb(null, true);
    }
});

// Serve the upload form
app.get('/', (req, res) => {
    res.render('index', { error: null, success: null });
});

// Handle ZIP file upload
app.post('/upload', upload.single('zipfile'), (req, res) => {
    if (!req.file) {
        return res.render('index', { error: 'No file uploaded', success: null });
    }
    const filename = req.file.originalname;
    const filenameNoExt = path.parse(filename).name;
    const successMessage = `The file ${filename} has been uploaded.<br>You can access files inside the ZIP at: /view?file=[${filenameNoExt}.zip]/your_filename`;
    res.render('index', { error: null, success: successMessage });
});

// Serve files from ZIP
app.get('/view', async (req, res) => {
    const fileParam = req.query.file;
    if (!fileParam) {
        return res.status(400).send('Error: No file specified');
    }

    // Parse the file parameter: [filename.zip]/inner_file
    const match = fileParam.match(/^\[(.+?)\.zip\]\/(.+)$/);
    if (!match) {
        return res.status(400).send('Error: Invalid path format. Use format: [file.zip]/filename');
    }

    const zipFilename = match[1] + '.zip';
    const fileInsideZip = match[2];

    // Sanitize the inner file path to prevent directory traversal
    const sanitizedPath = path.normalize(fileInsideZip).replace(/^(\.\.[\/\\])+/, '');

    // Block dangerous file types
    const dangerousExtensions = ['php', 'php3', 'php4', 'php5', 'phtml', 'phar', 'inc', 'cgi', 'pl', 'py', 'asp', 'aspx', 'jsp'];
    const fileExtension = path.extname(sanitizedPath).toLowerCase().slice(1);
    if (dangerousExtensions.includes(fileExtension)) {
        return res.status(403).send('Error: This file type is not allowed for security reasons');
    }

    const zipPath = path.join(__dirname, 'uploads', zipFilename);
    if (!fs.existsSync(zipPath)) {
        return res.status(404).send('Error: ZIP file not found');
    }

    // Create a unique temporary directory
    const tempDir = path.join(__dirname, 'temp', Date.now().toString());
    await fs.ensureDir(tempDir);

    try {
        const zip = new AdmZip(zipPath);
        const zipEntries = zip.getEntries();

        // Find the requested file in the ZIP
        const entry = zipEntries.find(e => e.entryName === sanitizedPath);
        if (!entry) {
            return res.status(404).send('Error: Requested file not found in the archive');
        }

        // Extract the file to the temp directory
        zip.extractEntryTo(entry, tempDir, false, true);
        const requestedFilePath = path.join(tempDir, path.basename(sanitizedPath));

        if (!fs.existsSync(requestedFilePath)) {
            return res.status(404).send('Error: Requested file not found in the archive');
        }

        // Determine MIME type
        const mimetype = mime.lookup(requestedFilePath) || 'application/octet-stream';

        // Additional check for PHP content
        if (mimetype.includes('php') || mimetype.includes('x-httpd-php')) {
            return res.status(403).send('Error: PHP files are not allowed');
        }

        // Serve the file and clean up
        res.sendFile(requestedFilePath, {
            headers: { 'Content-Type': mimetype }
        }, (err) => {
            if (err) {
                console.error(err);
                res.status(500).send('Error serving file');
            }
            fs.remove(tempDir); // Clean up temp directory
        });
    } catch (err) {
        console.error(err);
        res.status(500).send('Error processing ZIP file');
        fs.remove(tempDir); // Clean up on error
    }
});

// Start the server
app.listen(port, () => {
    console.log(`Server running at http://localhost:${port}`);
});
