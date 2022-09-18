export function api<T>(url: string, request: RequestInit): Promise<T> {
    return fetch(url, request)
        .then(response => {
            if (!response.ok) {
                throw new Error(response.statusText)
            }
            return response.json() as Promise<T>
        })
}
