<style>
.ap-tone-btn {
    padding: 10px 14px;
    border: 1.5px solid #e5e7eb;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 600;
    color: #6b7280;
    cursor: pointer;
    background: #fff;
    transition: all .15s;
    text-align: center;
}
.ap-tone-btn.active {
    border-color: #7c3aed;
    background: #f5f3ff;
    color: #7c3aed;
}
.sub-card {
    background: #fff;
    border-radius: 16px;
    padding: 20px;
    border: 1.5px solid #f0f0f0;
    box-shadow: 0 1px 4px rgba(0,0,0,.06);
    margin-bottom: 20px;
}
@keyframes sub-pulse {
    0%, 100% { opacity:1; transform:scale(1); }
    50%       { opacity:.6; transform:scale(1.3); }
}
@keyframes upModal-in {
    from { opacity:0; transform:scale(.92) translateY(16px); }
    to   { opacity:1; transform:scale(1) translateY(0); }
}
</style>