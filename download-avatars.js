import https from 'https';
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

// Get __dirname equivalent in ES modules
const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const avatars = {
    'adventurer': [1, 2, 3, 4, 5, 6],
    'bottts': [1, 2],
    'micah': [1, 2],
    'pixel-art': [1, 2],
};

// Enhanced download function with timeout
const downloadAvatar = (style, seed) => {
    return new Promise((resolve, reject) => {
        const filename = `avatar-${style}-${seed}.svg`;
        const filepath = path.join(__dirname, 'public', 'images', 'avatars', filename);
        
        const url = `https://api.dicebear.com/7.x/${style}/svg?seed=${seed}&scale=80`;
        
        console.log(`⬇️  Downloading: ${filename}`);
        
        const request = https.get(url, (response) => {
            if (response.statusCode === 200) {
                const fileStream = fs.createWriteStream(filepath);
                response.pipe(fileStream);
                
                fileStream.on('finish', () => {
                    fileStream.close();
                    console.log(`✅ Success: ${filename}`);
                    resolve({ style, seed, success: true });
                });
                
                fileStream.on('error', (err) => {
                    fs.unlink(filepath, () => {}); // Delete partial file
                    reject(new Error(`File write error: ${err.message}`));
                });
                
            } else {
                reject(new Error(`HTTP ${response.statusCode}`));
            }
        });
        
        request.on('error', (err) => {
            reject(new Error(`Request failed: ${err.message}`));
        });
        
        // Set timeout (10 seconds)
        request.setTimeout(10000, () => {
            request.destroy();
            reject(new Error('Request timeout'));
        });
    });
};

const downloadAll = async () => {
    // Create directory
    const dir = path.join(__dirname, 'public', 'images', 'avatars');
    if (!fs.existsSync(dir)) {
        fs.mkdirSync(dir, { recursive: true });
        console.log(`📁 Created directory: ${dir}`);
    }

    console.log('🚀 Starting DiceBear Avatar Download...');
    console.log('⏳ This may take a few seconds...\n');

    const results = {
        downloaded: 0,
        failed: 0,
        errors: []
    };

    // Download avatars sequentially with delays
    for (const [style, seeds] of Object.entries(avatars)) {
        console.log(`\n🎨 Downloading ${style.toUpperCase()} style...`);
        
        for (const seed of seeds) {
            try {
                await downloadAvatar(style, seed);
                results.downloaded++;
                
                // Respectful delay between requests
                await new Promise(resolve => setTimeout(resolve, 600));
                
            } catch (error) {
                results.failed++;
                results.errors.push(`avatar-${style}-${seed}.svg: ${error.message}`);
                console.error(`❌ Failed: avatar-${style}-${seed}.svg`);
            }
        }
    }

    // Summary
    console.log('\n' + '='.repeat(50));
    console.log('📊 DOWNLOAD SUMMARY');
    console.log('='.repeat(50));
    console.log(`✅ Successfully downloaded: ${results.downloaded} avatars`);
    console.log(`❌ Failed: ${results.failed} avatars`);
    console.log(`📁 Location: public/images/avatars/`);
    
    if (results.errors.length > 0) {
        console.log('\n❌ Errors:');
        results.errors.forEach(error => console.log(`   - ${error}`));
    }
    
    console.log('\n🎉 Avatar download completed!');
    console.log('💡 Update your Profile.vue to use local avatar paths.');
};

// Handle uncaught errors
process.on('uncaughtException', (error) => {
    console.error('💥 Uncaught Exception:', error.message);
    process.exit(1);
});

process.on('unhandledRejection', (reason, promise) => {
    console.error('💥 Unhandled Rejection at:', promise, 'reason:', reason);
    process.exit(1);
});

// Run the script
downloadAll();