# TamaEagle - Firebase Firestore Database

This project has been configured to work exclusively with Firebase Firestore as its database system.

## Current Setup

The application uses Firebase Firestore for all data storage operations:

## Setting Up Firebase

To enable Firebase Firestore integration:

1. **Install PHP gRPC Extension** (Required):
   - For Windows:
     ```
     pecl install grpc
     ```
   - Add `extension=grpc` to your php.ini file
   - Restart your web server

2. **Install Composer Dependencies**:
   ```
   composer require kreait/firebase-php google/cloud-firestore
   ```

3. **Set Up Firebase Account and Project**:
   - Go to [Firebase Console](https://console.firebase.google.com/)
   - Create a new project or select an existing one
   - Set up Firestore Database
   - Enable Email/Password authentication

4. **Configure Firebase Credentials**:
   - In Firebase Console, go to Project Settings > Service Accounts
   - Click "Generate new private key" and download the JSON file
   - Replace the content of `firebase-credentials.json` with the downloaded file

5. **Verify Setup**:
   - Open `database-status.php` in your browser to check the current database configuration
   - If everything is set up correctly, it should show "Using Firebase Configuration"
   
   **Troubleshooting**:
   - If you see "Firebase initialization error: Unable to create a FirestoreClient", make sure the gRPC extension is installed correctly
   - Follow the instructions at https://cloud.google.com/php/grpc for specific details on your platform

## Database Structure

### Firebase Collections:

- `users`: User accounts
- `projects`: Projects belonging to users
- `sections`: Sections within projects
- `tasks`: Tasks that can belong to projects and sections

## Important gRPC Extension Information

The gRPC extension is **absolutely required** for Firebase Firestore to work properly. If you encounter errors related to Firestore initialization, it's likely because this extension is missing.

### Detailed Installation Instructions for gRPC

#### Windows Users:

1. **Determine your PHP version and architecture**:
   ```
   php -i | findstr "PHP Version"
   php -i | findstr "Architecture"
   ```

2. **Download the appropriate DLL**:
   - Visit [PECL Windows Downloads](https://pecl.php.net/package/grpc)
   - Select the version matching your PHP version and architecture
   - Download the ZIP file (e.g., php_grpc-1.43.0-8.1-ts-vs16-x64.zip)

3. **Install the extension**:
   - Extract the ZIP file
   - Copy `php_grpc.dll` to your PHP extension directory (typically `ext` folder)
   - Open your `php.ini` file and add: `extension=grpc`
   - Restart your web server

4. **Verify installation**:
   ```
   php -m | findstr grpc
   ```
   Should display "grpc" if installed correctly

#### Linux/macOS Users:

1. **Install the extension**:
   ```
   pecl install grpc
   ```

2. **Add to php.ini**:
   - Add `extension=grpc.so` to your php.ini file
   - Restart your web server

3. **Verify installation**:
   ```
   php -m | grep grpc
   ```

## Security Notes

- The `firebase-credentials.json` file contains sensitive information
- Never expose this file to the public or commit it to version control with real credentials
- Add it to your `.gitignore` file to prevent accidental exposure

## Troubleshooting

If you encounter any issues:

1. Check `database-status.php` to verify which database system is being used
2. Ensure Composer dependencies are properly installed
3. Verify that `firebase-credentials.json` contains valid credentials
4. Check that Firebase project settings match those in your configuration
