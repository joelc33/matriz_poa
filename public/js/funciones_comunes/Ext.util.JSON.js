/*!
* Ext JS Library 3.4.1
* Copyright(c) 2006-2013 Ext JS, Inc.
* licensing@extjs.com
* http://www.extjs.com/license
*/
/**
* @class Ext.util.JSON
* Modified version of Douglas Crockford"s json.js that doesn"t
* mess with the Object prototype
* http://www.json.org/js.html
* @singleton
*/
var json_parse = (function () {

// This is a function that can parse a JSON text, producing a JavaScript
// data structure. It is a simple, recursive descent parser. It does not use
// eval or regular expressions, so it can be used as a model for implementing
// a JSON parser in other languages.

// We are defining the function inside of another function to avoid creating
// global variables.

var at, // The index of the current character
ch, // The current character
escapee = {
'"': '"',
'\\': '\\',
'/': '/',
b: '\b',
f: '\f',
n: '\n',
r: '\r',
t: '\t'
},
text,

error = function (m) {

// Call error when something is wrong.

throw {
name: 'SyntaxError',
message: m,
at: at,
text: text
};
},

next = function (c) {

// If a c parameter is provided, verify that it matches the current character.

if (c && c !== ch) {
error("Expected '" + c + "' instead of '" + ch + "'");
}

// Get the next character. When there are no more characters,
// return the empty string.

ch = text.charAt(at);
at += 1;
return ch;
},

number = function () {

// Parse a number value.

var number,
string = '';

if (ch === '-') {
string = '-';
next('-');
}
while (ch >= '0' && ch <= '9') {
string += ch;
next();
}
if (ch === '.') {
string += '.';
while (next() && ch >= '0' && ch <= '9') {
string += ch;
}
}
if (ch === 'e' || ch === 'E') {
string += ch;
next();
if (ch === '-' || ch === '+') {
string += ch;
next();
}
while (ch >= '0' && ch <= '9') {
string += ch;
next();
}
}
number = +string;
if (isNaN(number)) {
error("Bad number");
} else {
return number;
}
},

string = function () {

// Parse a string value.

var hex,
i,
string = '',
uffff;

// When parsing for string values, we must look for " and \ characters.

if (ch === '"') {
while (next()) {
if (ch === '"') {
next();
return string;
} else if (ch === '\\') {
next();
if (ch === 'u') {
uffff = 0;
for (i = 0; i < 4; i += 1) {
hex = parseInt(next(), 16);
if (!isFinite(hex)) {
break;
}
uffff = uffff * 16 + hex;
}
string += String.fromCharCode(uffff);
} else if (typeof escapee[ch] === 'string') {
string += escapee[ch];
} else {
break;
}
} else {
string += ch;
}
}
}
error("Bad string");
},

white = function () {

// Skip whitespace.

while (ch && ch <= ' ') {
next();
}
},

word = function () {

// true, false, or null.

switch (ch) {
case 't':
next('t');
next('r');
next('u');
next('e');
return true;
case 'f':
next('f');
next('a');
next('l');
next('s');
next('e');
return false;
case 'n':
next('n');
next('u');
next('l');
next('l');
return null;
}
error("Unexpected '" + ch + "'");
},

value, // Place holder for the value function.

array = function () {

// Parse an array value.

var array = [];

if (ch === '[') {
next('[');
white();
if (ch === ']') {
next(']');
return array; // empty array
}
while (ch) {
array.push(value());
white();
if (ch === ']') {
next(']');
return array;
}
next(',');
white();
}
}
error("Bad array");
},

object = function () {

// Parse an object value.

var key,
object = {};

if (ch === '{') {
next('{');
white();
if (ch === '}') {
next('}');
return object; // empty object
}
while (ch) {
key = string();
white();
next(':');
if (Object.hasOwnProperty.call(object, key)) {
error('Duplicate key "' + key + '"');
}
object[key] = value();
white();
if (ch === '}') {
next('}');
return object;
}
next(',');
white();
}
}
error("Bad object");
};

value = function () {

// Parse a JSON value. It could be an object, an array, a string, a number,
// or a word.

white();
switch (ch) {
case '{':
return object();
case '[':
return array();
case '"':
return string();
case '-':
return number();
default:
return ch >= '0' && ch <= '9' ? number() : word();
}
};

// Return the json_parse function. It will have access to all of the above
// functions and variables.

return function (source, reviver) {
var result;

text = source;
at = 0;
ch = ' ';
result = value();
white();
if (ch) {
error("Syntax error");
}

// If there is a reviver function, we recursively walk the new structure,
// passing each name/value pair to the reviver function for possible
// transformation, starting with a temporary root object that holds the result
// in an empty key. If there is not a reviver function, we simply return the
// result.

return typeof reviver === 'function' ? (function walk(holder, key) {
var k, v, value = holder[key];
if (value && typeof value === 'object') {
for (k in value) {
if (Object.hasOwnProperty.call(value, k)) {
v = walk(value, k);
if (v !== undefined) {
value[k] = v;
} else {
delete value[k];
}
}
}
}
return reviver.call(holder, key, value);
}({'': result}, '')) : result;
};
}());

Ext.util.JSON = new (function(){
var useHasOwn = !!{}.hasOwnProperty,
isNative = function() {
var useNative = null;

return function() {
if (useNative === null) {
useNative = Ext.USE_NATIVE_JSON && window.JSON && JSON.toString() == '[object JSON]';
}

return useNative;
};
}(),
pad = function(n) {
return n < 10 ? "0" + n : n;
},
doDecode = function(json){
return json_parse(json);
//return eval("(" + json + ')');
},
doEncode = function(o){
if(!Ext.isDefined(o) || o === null){
return "null";
}else if(Ext.isArray(o)){
return encodeArray(o);
}else if(Ext.isDate(o)){
return Ext.util.JSON.encodeDate(o);
}else if(Ext.isString(o)){
return encodeString(o);
}else if(typeof o == "number"){
//don't use isNumber here, since finite checks happen inside isNumber
return isFinite(o) ? String(o) : "null";
}else if(Ext.isBoolean(o)){
return String(o);
}else {
var a = ["{"], b, i, v;
for (i in o) {
// don't encode DOM objects
if(!o.getElementsByTagName){
if(!useHasOwn || o.hasOwnProperty(i)) {
v = o[i];
switch (typeof v) {
case "undefined":
case "function":
case "unknown":
break;
default:
if(b){
a.push(',');
}
a.push(doEncode(i), ":",
v === null ? "null" : doEncode(v));
b = true;
}
}
}
}
a.push("}");
return a.join("");
}
},
m = {
"\b": '\\b',
"\t": '\\t',
"\n": '\\n',
"\f": '\\f',
"\r": '\\r',
'"' : '\\"',
"\\": '\\\\'
},
encodeString = function(s){
if (/["\\\x00-\x1f]/.test(s)) {
return '"' + s.replace(/([\x00-\x1f\\"])/g, function(a, b) {
var c = m[b];
if(c){
return c;
}
c = b.charCodeAt();
return "\\u00" +
Math.floor(c / 16).toString(16) +
(c % 16).toString(16);
}) + '"';
}
return '"' + s + '"';
},
encodeArray = function(o){
var a = ["["], b, i, l = o.length, v;
for (i = 0; i < l; i += 1) {
v = o[i];
switch (typeof v) {
case "undefined":
case "function":
case "unknown":
break;
default:
if (b) {
a.push(',');
}
a.push(v === null ? "null" : Ext.util.JSON.encode(v));
b = true;
}
}
a.push("]");
return a.join("");
};

/**
* <p>Encodes a Date. This returns the actual string which is inserted into the JSON string as the literal expression.
* <b>The returned value includes enclosing double quotation marks.</b></p>
* <p>The default return format is "yyyy-mm-ddThh:mm:ss".</p>
* <p>To override this:</p><pre><code>
Ext.util.JSON.encodeDate = function(d) {
return d.format('"Y-m-d"');
};
</code></pre>
* @param {Date} d The Date to encode
* @return {String} The string literal to use in a JSON string.
*/
this.encodeDate = function(o){
return '"' + o.getFullYear() + "-" +
pad(o.getMonth() + 1) + "-" +
pad(o.getDate()) + "T" +
pad(o.getHours()) + ":" +
pad(o.getMinutes()) + ":" +
pad(o.getSeconds()) + '"';
};

/**
* Encodes an Object, Array or other value
* @param {Mixed} o The variable to encode
* @return {String} The JSON string
*/
this.encode = function() {
var ec;
return function(o) {
if (!ec) {
// setup encoding function on first access
ec = isNative() ? JSON.stringify : doEncode;
}
return ec(o);
};
}();


/**
* Decodes (parses) a JSON string to an object. If the JSON is invalid, this function throws a SyntaxError unless the safe option is set.
* @param {String} json The JSON string
* @return {Object} The resulting object
*/
this.decode = function() {
var dc;
return function(json) {
if (!dc) {
// setup decoding function on first access
dc = isNative() ? JSON.parse : doDecode;
}
return dc(json);
};
}();

})();
/**
* Shorthand for {@link Ext.util.JSON#encode}
* @param {Mixed} o The variable to encode
* @return {String} The JSON string
* @member Ext
* @method encode
*/
Ext.encode = Ext.util.JSON.encode;
/**
* Shorthand for {@link Ext.util.JSON#decode}
* @param {String} json The JSON string
* @param {Boolean} safe (optional) Whether to return null or throw an exception if the JSON is invalid.
* @return {Object} The resulting object
* @member Ext
* @method decode
*/
Ext.decode = Ext.util.JSON.decode;
