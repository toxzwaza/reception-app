<template>
  <div class="seal-overlay-container">
    <!-- 元画像表示エリア -->
    <div class="image-container" ref="imageContainer">
      <img 
        :src="originalImageUrl" 
        alt="元画像"
        class="original-image"
        @load="onImageLoad"
        @click="onImageClick"
        ref="originalImage"
      />
      
      <!-- 電子印オーバーレイ -->
      <div 
        v-for="(seal, index) in seals" 
        :key="index"
        class="seal-overlay"
        :style="{
          left: (seal.x / imageScale.x) + 'px',
          top: (seal.y / imageScale.y) + 'px',
          width: (seal.width / imageScale.x) + 'px',
          height: (seal.height / imageScale.y) + 'px',
          transform: `rotate(${seal.rotation}deg)`
        }"
        @mousedown="startDrag($event, index)"
        @click.stop
      >
        <img 
          :src="sealImageUrl" 
          alt="電子印"
          class="seal-image"
          :style="{
            width: '100%',
            height: '100%',
            objectFit: 'contain'
          }"
        />
        
        <!-- バウンディングボックス -->
        <div 
          v-if="selectedSealIndex === index"
          class="bounding-box"
          @mousedown.stop
        >
          <!-- リサイズハンドル -->
          <div class="resize-handle nw" @mousedown="startResize($event, index, 'nw')"></div>
          <div class="resize-handle ne" @mousedown="startResize($event, index, 'ne')"></div>
          <div class="resize-handle sw" @mousedown="startResize($event, index, 'sw')"></div>
          <div class="resize-handle se" @mousedown="startResize($event, index, 'se')"></div>
          
          <!-- 回転ハンドル -->
          <div class="rotate-handle" @mousedown="startRotate($event, index)"></div>
          
          <!-- 削除ボタン -->
          <button 
            class="delete-button"
            @click="removeSeal(index)"
            title="削除"
          >
            ×
          </button>
        </div>
      </div>
    </div>
    
    <!-- コントロールパネル -->
    <div class="control-panel">
      <div class="control-group">
        <label>電子印サイズ:</label>
        <input 
          type="range" 
          v-model="sealSize" 
          min="50" 
          max="200" 
          step="10"
          @input="updateSealSize"
        />
        <span>{{ sealSize }}px</span>
      </div>
      
      <div class="control-group">
        <label>透明度:</label>
        <input 
          type="range" 
          v-model="sealOpacity" 
          min="0.3" 
          max="1" 
          step="0.1"
          @input="updateSealOpacity"
        />
        <span>{{ Math.round(sealOpacity * 100) }}%</span>
      </div>
      
      <div class="control-group">
        <button @click="clearAllSeals" class="btn-clear">すべて削除</button>
        <button @click="saveSeals" class="btn-save">保存</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, onUnmounted } from 'vue';

const props = defineProps({
  originalImageUrl: {
    type: String,
    required: true
  },
  sealImageUrl: {
    type: String,
    default: '/storage/stamp/sealed.png'
  }
});

const emit = defineEmits(['save', 'cancel']);

// リアクティブデータ
const imageContainer = ref(null);
const originalImage = ref(null);
const seals = ref([]);
const selectedSealIndex = ref(-1);
const sealSize = ref(100);
const sealOpacity = ref(0.8);

// ドラッグ状態
const isDragging = ref(false);
const isResizing = ref(false);
const isRotating = ref(false);
const dragStart = reactive({ x: 0, y: 0 });
const resizeDirection = ref('');
const rotateStart = ref(0);

// 画像の実際のサイズと表示サイズ
const imageScale = ref({ x: 1, y: 1 });
const actualImageSize = ref({ width: 0, height: 0 });

// 画像読み込み完了
const onImageLoad = () => {
  console.log('画像が読み込まれました');
  
  // 画像の実際のサイズを取得
  actualImageSize.value = {
    width: originalImage.value.naturalWidth,
    height: originalImage.value.naturalHeight
  };
  
  // 表示サイズを取得
  const displayRect = originalImage.value.getBoundingClientRect();
  
  // スケール比を計算
  imageScale.value = {
    x: actualImageSize.value.width / displayRect.width,
    y: actualImageSize.value.height / displayRect.height
  };
  
  console.log('画像スケール:', imageScale.value);
  console.log('実際のサイズ:', actualImageSize.value);
  console.log('表示サイズ:', { width: displayRect.width, height: displayRect.height });
};

// 表示座標を実際の画像座標に変換
const convertDisplayToActual = (displayX, displayY, displayWidth, displayHeight) => {
  return {
    x: displayX * imageScale.value.x,
    y: displayY * imageScale.value.y,
    width: displayWidth * imageScale.value.x,
    height: displayHeight * imageScale.value.y
  };
};

// 実際の画像座標を表示座標に変換
const convertActualToDisplay = (actualX, actualY, actualWidth, actualHeight) => {
  return {
    x: actualX / imageScale.value.x,
    y: actualY / imageScale.value.y,
    width: actualWidth / imageScale.value.x,
    height: actualHeight / imageScale.value.y
  };
};

// 画像クリック時の電子印配置
const onImageClick = (event) => {
  if (isDragging.value || isResizing.value || isRotating.value) return;
  
  const rect = originalImage.value.getBoundingClientRect();
  const displayX = event.clientX - rect.left;
  const displayY = event.clientY - rect.top;
  
  // 表示座標を実際の画像座標に変換
  const actualCoords = convertDisplayToActual(
    displayX - sealSize.value / 2,
    displayY - sealSize.value / 2,
    sealSize.value,
    sealSize.value
  );
  
  // 新しい電子印を追加（実際の画像座標で保存）
  const newSeal = {
    x: actualCoords.x,
    y: actualCoords.y,
    width: actualCoords.width,
    height: actualCoords.height,
    rotation: 0,
    opacity: sealOpacity.value
  };
  
  seals.value.push(newSeal);
  selectedSealIndex.value = seals.value.length - 1;
  
  console.log('電子印配置:', {
    display: { x: displayX, y: displayY, size: sealSize.value },
    actual: actualCoords
  });
};

// ドラッグ開始
const startDrag = (event, index) => {
  if (isResizing.value || isRotating.value) return;
  
  isDragging.value = true;
  selectedSealIndex.value = index;
  
  // 表示座標でのドラッグ開始位置を記録
  const rect = originalImage.value.getBoundingClientRect();
  const displayX = event.clientX - rect.left;
  const displayY = event.clientY - rect.top;
  
  // 実際の画像座標に変換
  const actualX = displayX * imageScale.value.x;
  const actualY = displayY * imageScale.value.y;
  
  dragStart.x = actualX - seals.value[index].x;
  dragStart.y = actualY - seals.value[index].y;
  
  event.preventDefault();
};

// リサイズ開始
const startResize = (event, index, direction) => {
  isResizing.value = true;
  selectedSealIndex.value = index;
  resizeDirection.value = direction;
  
  // 表示座標でのリサイズ開始位置を記録
  const rect = originalImage.value.getBoundingClientRect();
  const displayX = event.clientX - rect.left;
  const displayY = event.clientY - rect.top;
  
  // 実際の画像座標に変換
  dragStart.x = displayX * imageScale.value.x;
  dragStart.y = displayY * imageScale.value.y;
  
  event.preventDefault();
  event.stopPropagation();
};

// 回転開始
const startRotate = (event, index) => {
  isRotating.value = true;
  selectedSealIndex.value = index;
  
  const seal = seals.value[index];
  const centerX = seal.x + seal.width / 2;
  const centerY = seal.y + seal.height / 2;
  
  rotateStart.value = Math.atan2(event.clientY - centerY, event.clientX - centerX) * 180 / Math.PI - seal.rotation;
  
  event.preventDefault();
  event.stopPropagation();
};

// マウス移動処理
const onMouseMove = (event) => {
  if (selectedSealIndex.value === -1) return;
  
  const seal = seals.value[selectedSealIndex.value];
  const rect = originalImage.value.getBoundingClientRect();
  const displayX = event.clientX - rect.left;
  const displayY = event.clientY - rect.top;
  
  // 実際の画像座標に変換
  const actualX = displayX * imageScale.value.x;
  const actualY = displayY * imageScale.value.y;
  
  if (isDragging.value) {
    seal.x = actualX - dragStart.x;
    seal.y = actualY - dragStart.y;
  } else if (isResizing.value) {
    const deltaX = actualX - dragStart.x;
    const deltaY = actualY - dragStart.y;
    
    switch (resizeDirection.value) {
      case 'nw':
        seal.x += deltaX;
        seal.y += deltaY;
        seal.width -= deltaX;
        seal.height -= deltaY;
        break;
      case 'ne':
        seal.y += deltaY;
        seal.width += deltaX;
        seal.height -= deltaY;
        break;
      case 'sw':
        seal.x += deltaX;
        seal.width -= deltaX;
        seal.height += deltaY;
        break;
      case 'se':
        seal.width += deltaX;
        seal.height += deltaY;
        break;
    }
    
    // 最小サイズ制限（実際の画像座標で）
    seal.width = Math.max(30 * imageScale.value.x, seal.width);
    seal.height = Math.max(30 * imageScale.value.y, seal.height);
    
    dragStart.x = actualX;
    dragStart.y = actualY;
  } else if (isRotating.value) {
    const centerX = seal.x + seal.width / 2;
    const centerY = seal.y + seal.height / 2;
    const angle = Math.atan2(actualY - centerY, actualX - centerX) * 180 / Math.PI;
    seal.rotation = angle - rotateStart.value;
  }
};

// マウスアップ処理
const onMouseUp = () => {
  isDragging.value = false;
  isResizing.value = false;
  isRotating.value = false;
  resizeDirection.value = '';
};

// 電子印削除
const removeSeal = (index) => {
  seals.value.splice(index, 1);
  if (selectedSealIndex.value >= seals.value.length) {
    selectedSealIndex.value = -1;
  }
};

// サイズ更新
const updateSealSize = () => {
  if (selectedSealIndex.value !== -1) {
    const seal = seals.value[selectedSealIndex.value];
    const centerX = seal.x + seal.width / 2;
    const centerY = seal.y + seal.height / 2;
    
    // 表示サイズを実際の画像サイズに変換
    const actualSize = sealSize.value * imageScale.value.x;
    
    seal.width = actualSize;
    seal.height = actualSize;
    seal.x = centerX - actualSize / 2;
    seal.y = centerY - actualSize / 2;
  }
};

// 透明度更新
const updateSealOpacity = () => {
  if (selectedSealIndex.value !== -1) {
    seals.value[selectedSealIndex.value].opacity = sealOpacity.value;
  }
};

// すべて削除
const clearAllSeals = () => {
  if (confirm('すべての電子印を削除しますか？')) {
    seals.value = [];
    selectedSealIndex.value = -1;
  }
};

// 保存
const saveSeals = () => {
  emit('save', seals.value);
};

// ライフサイクル
onMounted(() => {
  document.addEventListener('mousemove', onMouseMove);
  document.addEventListener('mouseup', onMouseUp);
});

onUnmounted(() => {
  document.removeEventListener('mousemove', onMouseMove);
  document.removeEventListener('mouseup', onMouseUp);
});
</script>

<style scoped>
.seal-overlay-container {
  max-width: 100%;
  margin: 0 auto;
}

.image-container {
  position: relative;
  display: inline-block;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  overflow: hidden;
  cursor: crosshair;
}

.original-image {
  max-width: 100%;
  height: auto;
  display: block;
}

.seal-overlay {
  position: absolute;
  cursor: move;
  z-index: 10;
}

.seal-image {
  opacity: v-bind(sealOpacity);
  pointer-events: none;
}

.bounding-box {
  position: absolute;
  top: -2px;
  left: -2px;
  right: -2px;
  bottom: -2px;
  border: 2px dashed #3b82f6;
  pointer-events: none;
}

.resize-handle {
  position: absolute;
  width: 8px;
  height: 8px;
  background: #3b82f6;
  border: 1px solid white;
  pointer-events: all;
  cursor: pointer;
}

.resize-handle.nw {
  top: -4px;
  left: -4px;
  cursor: nw-resize;
}

.resize-handle.ne {
  top: -4px;
  right: -4px;
  cursor: ne-resize;
}

.resize-handle.sw {
  bottom: -4px;
  left: -4px;
  cursor: sw-resize;
}

.resize-handle.se {
  bottom: -4px;
  right: -4px;
  cursor: se-resize;
}

.rotate-handle {
  position: absolute;
  top: -20px;
  left: 50%;
  transform: translateX(-50%);
  width: 12px;
  height: 12px;
  background: #10b981;
  border: 1px solid white;
  border-radius: 50%;
  pointer-events: all;
  cursor: pointer;
}

.delete-button {
  position: absolute;
  top: -8px;
  right: -8px;
  width: 20px;
  height: 20px;
  background: #ef4444;
  color: white;
  border: none;
  border-radius: 50%;
  cursor: pointer;
  font-size: 12px;
  line-height: 1;
  pointer-events: all;
}

.control-panel {
  margin-top: 16px;
  padding: 16px;
  background: #f9fafb;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
}

.control-group {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 12px;
}

.control-group:last-child {
  margin-bottom: 0;
}

.control-group label {
  font-weight: 500;
  min-width: 80px;
}

.control-group input[type="range"] {
  flex: 1;
  max-width: 200px;
}

.control-group span {
  min-width: 40px;
  text-align: right;
  font-size: 14px;
  color: #6b7280;
}

.btn-clear, .btn-save {
  padding: 8px 16px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 500;
}

.btn-clear {
  background: #ef4444;
  color: white;
}

.btn-save {
  background: #10b981;
  color: white;
}

.btn-clear:hover {
  background: #dc2626;
}

.btn-save:hover {
  background: #059669;
}
</style>
