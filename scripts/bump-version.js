const fs = require("fs");
const file = "aidev-plugin-starter/aidev-plugin-starter.php";
const version = process.argv[2];
if (!version) { throw new Error("Missing version"); }
let s = fs.readFileSync(file, "utf8");
// Header:  * Version: x.y.z
s = s.replace(/^( \* Version:\s*)(.*)$/m, `$1${version}`);
// Optional: define('AIDEV_PS_VERSION', 'x.y.z');
s = s.replace(/(define\(\s*'AIDEV_PS_VERSION'\s*,\s*')[^']+('\s*\)\s*;)/, `$1${version}$2`);
fs.writeFileSync(file, s, { encoding: "utf8" });
console.log(`Updated plugin header to ${version}`);