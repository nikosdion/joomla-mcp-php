# Notes for the `#[Schema]` attribute

For the format types, please refer to the [JSON Schema documentation](https://tour.json-schema.org/content/08-Annotating-JSON-Schemas/04-format-and-examples) and this [third party resource](https://opis.io/json-schema/2.x/formats.html).

The formats supported by the `#[Schema]` attribute appear to be the following:

* **Date and time formats**: date-time, date, time, duration
* **Email formats**: email, idn-email
* **Hostname formats**: hostname, idn-hostname
* **IP address formats**: ipv4, ipv6
* **Resource identifier formats**: uuid, uri, uri-reference, uri-template, iri, iri-reference
* **Other**: regex, json-pointer, relative-json-pointer

View more about the formats [here](https://json-schema.org/understanding-json-schema/reference/string#built-in-formats).

See `\PhpMcp\Server\Attributes\Schema` for PHP attribute implementation.