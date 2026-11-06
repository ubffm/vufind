composer install
npm install --allow-git=root
npm run build
npm run build:scss -- --themes bootstrap5

ln -s ../themes public/themes
