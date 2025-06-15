@props(['userId'])
<button @click="$dispatch('edit-user-modal', {userId: {{$userId}}})">edit</button>