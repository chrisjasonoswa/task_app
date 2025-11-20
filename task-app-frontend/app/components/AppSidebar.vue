<template>
  <Sidebar collapsible="none" class="p-4 pt-6 bg-transparent">
    <SidebarContent class="bg-transparent">
      <!-- Loop through the final grouped sections -->
      <div
        v-for="(dates, section) in groupedDates"
        :key="section"
        class="mb-4"
      >
        <div class="px-2 text-xs text-neutral-400 -300 mb-2" v-if="section != 'This Week'">
          {{ section }}
        </div>

        <SidebarMenu>
          <SidebarMenuItem 
            v-for="date in dates" 
            :key="date"
          >
            <SidebarMenuButton
              as-child
              @click="handleUpdateActiveDate(date)"
              :is-active="activeDate === date"
              :class="`${activeDate === date ? 'bg-black/90! text-white!' : ''} hover:cursor-pointer`"
            >
              <a>{{ displayDate(date) }}</a>
            </SidebarMenuButton>
          </SidebarMenuItem>
        </SidebarMenu>
      </div>

    </SidebarContent>
  </Sidebar>
</template>

<script setup lang="ts">
import {
  Sidebar,
  SidebarContent,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
} from '@/components/ui/sidebar'

import dayjs from 'dayjs'
import isoWeek from 'dayjs/plugin/isoWeek'
import weekOfYear from 'dayjs/plugin/weekOfYear'
dayjs.extend(isoWeek)
dayjs.extend(weekOfYear)

// ---- Props / Emits ----
const props = defineProps<{
  taskDates: string[]
  activeDate: string
}>()

const activeDate = defineModel<string>('active-date')
const handleUpdateActiveDate = (date: string) => {
  activeDate.value = date
}

// Display formatting rules
const displayDate = (date: string) => {
  const d = dayjs(date)
  const today = dayjs()
  const yesterday = today.subtract(1, 'day')

  if (d.isSame(today, 'day')) return 'Today'
  if (d.isSame(yesterday, 'day')) return 'Yesterday'

  return d.format('dddd, MMMM D')
}

// ----------------------------
// GROUPING RULES:
// ----------------------------
// Today
// Yesterday
// Last Week
// 2 weeks ago → Nth Week of Month
// Older
// ----------------------------
const groupedDates = computed(() => {
  const today = dayjs()
  const yesterday = today.subtract(1, 'day')

  const thisWeek = today.isoWeek()
  const lastWeek = thisWeek - 1
  const twoWeeksAgo = thisWeek - 2

  const groups: Record<string, string[]> = {}

  // Helper to ensure section exists
  const addToGroup = (section: string, date: string) => {
    if (!groups[section]) groups[section] = []
    groups[section].push(date)
  }

  for (const dateStr of props.taskDates) {
    const date = dayjs(dateStr)
    const weekNum = date.isoWeek()

    // TODAY
    if (weekNum === thisWeek) {
      addToGroup('This Week', dateStr)
      continue
    }

    // LAST WEEK
    if (weekNum === lastWeek) {
      addToGroup('Last Week', dateStr)
      continue
    }

    // 2 WEEKS AGO → Nth Week of {Month}
    if (weekNum === twoWeeksAgo) {
      let n = Math.ceil(date.date() / 7)
      let nth = ''
      if (n % 10 === 1 && n !== 11) nth = `${n}st`
      else if (n % 10 === 2 && n !== 12) nth = `${n}nd`
      else if (n % 10 === 3 && n !== 13) nth = `${n}rd`
      else nth = `${n}th` 
      const month = date.format('MMMM')
      addToGroup(`${nth} Week of ${month}`, dateStr)
      continue
    }

    // OLDER
    addToGroup('Older', dateStr)
  }

  return groups
})
</script>
