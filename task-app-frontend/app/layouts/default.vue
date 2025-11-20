<template>
  <div class="h-screen">
    <nav class="h-16 px-8 py-2 flex flex-1 items-center justify-between border-b-2 border-gray-200">
      <img src="/logo.png" class="w-8 aspect-square" />
      <div class="grid w-full max-w-sm gap-6">
        <InputGroup >
          <InputGroupInput placeholder="Search" v-model="search"/>
          <InputGroupAddon class="rounded-xl">
            <Search />
          </InputGroupAddon>
        </InputGroup>
      </div>
      <Avatar>
        <AvatarImage src="https://radix.shadcn-vue.com/avatars/01.png" alt="@shadcn" />
        <AvatarFallback>CN</AvatarFallback>
      </Avatar>
    </nav>
    <main>
      <slot/>
    </main>
  </div>
</template>


<script setup lang="ts">
import { Search } from 'lucide-vue-next'
import { InputGroup, InputGroupAddon, InputGroupInput, } from '@/components/ui/input-group'
import {
  Avatar,
  AvatarFallback,
  AvatarImage,
} from '@/components/ui/avatar'
const search = defineModel<string>('search')
</script>
