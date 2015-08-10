<?hh
async funciton get_raw(string $url): Awaitable<string> {
  return await HH\Asio\curl_exec($url);
}

function join_main(): void {
  $result = HH\Asio\join(get_raw("http://www.google.com));
  var_dump($result);
}