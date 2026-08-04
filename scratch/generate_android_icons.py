from PIL import Image
import os

src_path = 'public/images/mobile_icon_alfarezmart.png'
img = Image.open(src_path).convert('RGBA')

# Mipmap sizes for launcher icons
mipmaps = {
    'mipmap-mdpi': (48, 108),
    'mipmap-hdpi': (72, 162),
    'mipmap-xhdpi': (96, 216),
    'mipmap-xxhdpi': (144, 324),
    'mipmap-xxxhdpi': (192, 432),
}

res_base = 'android/app/src/main/res'

for folder, (size_icon, size_fg) in mipmaps.items():
    folder_path = os.path.join(res_base, folder)
    os.makedirs(folder_path, exist_ok=True)
    
    # 1. Standard square icon
    icon_img = img.resize((size_icon, size_icon), Image.Resampling.LANCZOS)
    icon_img.save(os.path.join(folder_path, 'ic_launcher.png'))
    icon_img.save(os.path.join(folder_path, 'ic_launcher_round.png'))
    
    # 2. Foreground icon for adaptive icons
    fg_img = img.resize((size_fg, size_fg), Image.Resampling.LANCZOS)
    fg_img.save(os.path.join(folder_path, 'ic_launcher_foreground.png'))
    print(f"Generated icons for {folder}: {size_icon}x{size_icon} and {size_fg}x{size_fg}")

# Update splash images
splash_folders = [
    'drawable', 'drawable-land-mdpi', 'drawable-land-hdpi', 'drawable-land-xhdpi',
    'drawable-land-xxhdpi', 'drawable-land-xxxhdpi', 'drawable-port-mdpi',
    'drawable-port-hdpi', 'drawable-port-xhdpi', 'drawable-port-xxhdpi', 'drawable-port-xxxhdpi'
]

for sf in splash_folders:
    sf_path = os.path.join(res_base, sf)
    os.makedirs(sf_path, exist_ok=True)
    splash_img = img.resize((512, 512), Image.Resampling.LANCZOS)
    splash_img.save(os.path.join(sf_path, 'splash.png'))

print("All Android icons & splash images generated successfully!")
