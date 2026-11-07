<template>
<div class="dashboard-container flex flex-wrap gap-4 p-4">
    <el-card v-loading="loading" element-loading-text="执行中,请稍后..."
      class="tool-card" :class="{'is-loading': loading}" @click="repairBottomEvent">
      <div class="card-bg"></div>
      <div class="card-content">
        <div class="icon-container">
          <el-icon><Refresh /></el-icon>
        </div>
        <div class="card-info">
          <h3 class="card-title">装修修复</h3>
          <p class="card-desc">底部导航和装修页面数据修复</p>
        </div>
      </div>
    </el-card>
  </div>
</template>

<script lang="ts" setup>
import { ref } from 'vue'
import { repairBottom } from '@/addon/tk_upgrade/api/index'
const loading = ref(false)
const repairBottomEvent = async () => {
  loading.value = true
  await repairBottom()
  loading.value = false
  ElMessage.success('操作成功')
}
</script>

<style lang="scss" scoped>
.dashboard-container {
  background-color: #f9fafb;
  min-height: 100%;
  position: relative;
  
  &::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-image: 
      radial-gradient(#e5e7eb 1px, transparent 1px), 
      radial-gradient(#e5e7eb 1px, transparent 1px);
    background-size: 20px 20px;
    background-position: 0 0, 10px 10px;
    opacity: 0.5;
    pointer-events: none;
  }
}

.tool-card {
  width: 280px;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
  transition: all 0.3s ease;
  cursor: pointer;
  position: relative;
  border: 1px solid #e5e7eb;
  background: rgba(255, 255, 255, 0.8);
  backdrop-filter: blur(10px);
  
  &:hover {
    border: 2px solid #6366f1;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  }
  
  &.is-loading {
    opacity: 0.8;
  }
}

.card-bg {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  opacity: 0.05;
  transition: all 0.5s ease;
  z-index: 0;
  
  &::after {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    right: -50%;
    bottom: -50%;
    background: radial-gradient(circle, rgba(255,255,255,0.8) 0%, rgba(255,255,255,0) 70%);
    mix-blend-mode: overlay;
    opacity: 0;
    transition: opacity 0.5s ease;
  }
}

.card-content {
  display: flex;
  align-items: center;
  border-radius:12px;
 // padding: 20px;
  position: relative;
  z-index: 1;
}

.icon-container {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 50px;
  height: 50px;
  border-radius: 12px;
  background: rgba(99, 102, 241, 0.1);
  margin-right: 16px;
  position: relative;
  overflow: hidden;
  
  &::before {
    content: '';
    position: absolute;
    top: -10px;
    left: -10px;
    right: -10px;
    bottom: -10px;
    background: linear-gradient(45deg, rgba(99,102,241,0.3) 0%, rgba(99,102,241,0) 70%);
    animation: rotate 8s linear infinite;
  }
  
  .el-icon {
    font-size: 24px;
    color: #6366f1;
    position: relative;
  }
}

.card-info {
  flex: 1;
}

.card-title {
  font-size: 16px;
  font-weight: 600;
  color: #1f2937;
  margin: 0 0 4px 0;
}

.card-desc {
  font-size: 13px;
  color: #6b7280;
  margin: 0;
}

@keyframes rotate {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}
</style>
