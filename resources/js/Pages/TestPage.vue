<template>
  <div style="padding: 40px; font-family: sans-serif;">
    <h1>🎉 Vue + Inertia شغّال!</h1>
    <p>أهلاً <strong>{{ user.name }}</strong></p>
    <p>هذا الـ component جاي من Vue، والبيانات جاية من Laravel مباشرة.</p>
  </div>
</template>

<script setup>
/*
 * defineProps — هكذا Vue تستقبل البيانات من Laravel
 * نفس فكرة compact() في الـ Controller
 * بس بدل ما تجي في $variable — بتجي كـ props
 */
defineProps({
  user: Object,
})
</script>