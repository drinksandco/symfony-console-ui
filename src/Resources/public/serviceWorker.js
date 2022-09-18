self.addEventListener("install", installEvent => {
    console.log('installing webapp')
    // installEvent.waitUntil(
    //     caches.open(staticDevCoffee).then(cache => {
    //         cache.addAll(assets)
    //     })
    // )
})